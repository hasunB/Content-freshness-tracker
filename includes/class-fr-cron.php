<?php

if (! defined('ABSPATH')) exit;

class FR_Cron
{

    public static function get_default()
    {
        return array(
            'stale_after_value' => 1,                   // Default duration number
            'stale_after_unit'  => 'months',             // Default unit (minute, hour, day, week, month, year)
            'post_types'        => array('post'),     // Default post type
            'schedule'          => 'every_five_minutes', // Custom cron schedule interval
            'clear_reviewed'    => 'never',             // Disabled by default
            'email_notify'      => 0,                   // Disabled by default
            'roles'             => array('administrator', 'editor'), // Default notification roles
        );
    }

    public static function register_custom_schedules($schedules)
    {
        $schedules['every_five_minutes'] = array(
            'interval' => 5 * 60,
            'display'  => __('Every 5 Minutes', 'fresh-reminder'),
        );
        $schedules['every_fifteen_minutes'] = array(
            'interval' => 15 * 60,
            'display'  => __('Every 15 Minutes', 'fresh-reminder'),
        );
        $schedules['every_thirty_minutes'] = array(
            'interval' => 30 * 60,
            'display'  => __('Every 30 Minutes', 'fresh-reminder'),
        );
        $schedules['hourly'] = array(
            'interval' => 60 * 60,
            'display'  => __('Hourly', 'fresh-reminder'),
        );
        $schedules['daily'] = array(
            'interval' => 24 * 60 * 60,
            'display'  => __('Daily', 'fresh-reminder'),
        );

        return $schedules;
    }

    public static function activate()
    {
        FR_Logger::log("Fresh Reminder plugin activated", "info");

        // Load settings or defaults
        $settings = get_option(FR_OPTION_NAME, self::get_default());
        $schedule = !empty($settings['schedule']) ? $settings['schedule'] : 'every_five_minutes';
        $clear_reviewed = !empty($settings['clear_reviewed']) ? $settings['clear_reviewed'] : 'never';

        // Double-check that the selected schedule exists
        $schedules = wp_get_schedules();
        if (! isset($schedules[$schedule])) {
            $schedule = 'daily'; // fallback
            FR_Logger::log("Invalid schedule, defaulted to 'daily'", "error");
        }

        // Prevent duplicate event
        if (! wp_next_scheduled('fr_check_event')) {
            wp_schedule_event(time(), $schedule, 'fr_check_event');
            FR_Logger::log("Scheduled 'fr_check_event' with interval: " . $schedule, "info");
        }

        if ($clear_reviewed != 'never') {
            if (! wp_next_scheduled('fr_clear_reviewed_event')) {
                wp_schedule_event(time(), $clear_reviewed, 'fr_clear_reviewed_event');
                FR_Logger::log("Scheduled 'fr_clear_reviewed_event' with interval: " . $clear_reviewed, "info");
            }
        }
    }

    public static function deactivate()
    {
        FR_Logger::log("Fresh Reminder plugin deactivated", "info");
        wp_clear_scheduled_hook('fr_check_event');
    }


    public static function check_stale_posts()
    {

        try {
            // Load settings or defaults)
            $settings = get_option(FR_OPTION_NAME, self::get_default());
            $stale_value = max(1, intval($settings['stale_after_value']));
            $stale_unit  = isset($settings['stale_after_unit']) ? $settings['stale_after_unit'] : 'months';
            $post_types  = ! empty($settings['post_types']) ? $settings['post_types'] : array('post');

            // Calculate cutoff date dynamically
            $before = gmdate('Y-m-d H:i:s', strtotime("-{$stale_value} {$stale_unit}"));

            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'date_query'     => array(array('before' => $before)),
                'fields'         => 'ids',
                'posts_per_page' => -1,
            );

            //fetch data from DB
            $q = new WP_Query($args);
            $ids = $q->posts ? $q->posts : array();

            // Filter out posts that were reviewed AFTER last modification
            $filtered = array();
            $current_time = time();
            $stale_interval = strtotime("-{$stale_value} {$stale_unit}", $current_time);

            foreach ($ids as $id) {
                $reviewed      = get_post_meta($id, '_fr_reviewed', true);
                $post_modified = strtotime(get_post_field('post_modified', $id));

                // If the post has been reviewed before
                if ($reviewed) {
                    $reviewed_time = intval($reviewed);

                    // If post modified AFTER review → remove meta, mark stale
                    // if ( $post_modified > $reviewed_time ) {
                    //     delete_post_meta( $id, '_fr_reviewed' );
                    //     $filtered[] = $id;
                    //     continue;
                    // }

                    //If review is older than allowed stale time → remove meta, mark stale
                    if ($reviewed_time <= $stale_interval) {
                        delete_post_meta($id, '_fr_reviewed');
                        $filtered[] = $id;
                        continue;
                    }
                } else {
                    // No review meta → automatically stale
                    $filtered[] = $id;
                }
            }

            update_option(FR_CACHE_OPTION, array('timestamp' => time(), 'post_ids' => $filtered), false);

            if (! empty($settings['email_notify']) && ! empty($filtered)) {
                self::send_email_digest($filtered, $settings);
            }

            FR_Logger::log("checked stale posts", "info");
        } catch (Exception $e) {
            FR_Logger::log("Error in check_stale_posts: " . $e->getMessage(), "error");
        }
    }

    public static function remove_reviewed_content()
    {
        // Get current cache
        $cache = get_option(FR_CACHE_OPTION);

        if (empty($cache) || !isset($cache['post_ids']) || !is_array($cache['post_ids'])) {
            FR_Logger::log("No cache found or invalid cache structure.", "error");
            return;
        }

        $stale_ids = $cache['post_ids'];
        $updated_ids = array();

        foreach ($stale_ids as $post_id) {
            $is_reviewed = get_post_meta($post_id, '_fr_reviewed', true);
            $is_pinned   = get_post_meta($post_id, '_fr_pined', true);

            // Keep in cache only if not reviewed or pinned
            if (empty($is_reviewed) || !empty($is_pinned)) {
                $updated_ids[] = $post_id;
            } else {
                // Reviewed and not pinned → remove review tag and exclude from cache
                delete_post_meta($post_id, '_fr_reviewed');
            }
        }

        // Update cache only if it changed
        if ($updated_ids !== $stale_ids) {
            update_option(FR_CACHE_OPTION, array(
                'timestamp' => time(),
                'post_ids'  => array_values($updated_ids)
            ), false);

            FR_Logger::log("Cache updated — reviewed unpinned posts removed.", "info");
        } else {
            FR_Logger::log("No reviewed unpinned posts to remove from cache.", "warning");
        }
    }



    public static function send_email_digest($post_ids, $settings)
    {
        FR_Logger::log("send admin notify email", "info");
    }
}

/* ensure the scheduled hook` is always available */
add_action('fr_check_event', array('FR_Cron', 'check_stale_posts')); //a custom action
add_action('fr_clear_reviewed_event', array('FR_Cron', 'remove_reviewed_content')); //
