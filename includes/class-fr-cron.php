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
        error_log("Fresh Reminder plugin activated");

        // Load settings or defaults
        $settings = get_option(FR_OPTION_NAME, self::get_default());
        $schedule = !empty($settings['schedule']) ? $settings['schedule'] : 'every_five_minutes';
        $clear_reviewed = !empty($settings['clear_reviewed']) ? $settings['clear_reviewed'] : 'never';

        // Double-check that the selected schedule exists
        $schedules = wp_get_schedules();
        if (! isset($schedules[$schedule])) {
            $schedule = 'daily'; // fallback
            error_log("Invalid schedule, defaulted to 'daily'");
        }

        // Prevent duplicate event
        if (! wp_next_scheduled('fr_check_event')) {
            wp_schedule_event(time(), $schedule, 'fr_check_event');
            error_log("Scheduled 'fr_check_event' with interval: " . $schedule);
        }

        if ($clear_reviewed != 'never') {
            if (! wp_next_scheduled('fr_clear_reviewed_event')) {
                wp_schedule_event(time(), $clear_reviewed, 'fr_clear_reviewed_event');
                error_log("Scheduled 'fr_clear_reviewed_event' with interval: " . $clear_reviewed);
            }
        }
    }

    public static function deactivate()
    {
        error_log("Fresh Reminder plugin deactivated");
        wp_clear_scheduled_hook('fr_check_event');
    }


    public static function check_stale_posts()
    {
        $settings = get_option(FR_OPTION_NAME, self::get_default());
        $stale_value = max(1, intval($settings['stale_after_value']));
        $stale_unit  = isset($settings['stale_after_unit']) ? $settings['stale_after_unit'] : 'months';
        $post_types  = ! empty($settings['post_types']) ? $settings['post_types'] : array('post');

        // Calculate cutoff date dynamically
        $before = date('Y-m-d H:i:s', strtotime("-{$stale_value} {$stale_unit}"));

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

        foreach ($ids as $id) {
            $reviewed      = get_post_meta($id, 'fr_reviewed', true);
            $post_modified = get_post_field('post_modified', $id);

            // If reviewed AFTER modification, skip it
            if ($reviewed && intval($reviewed) >= strtotime($post_modified)) {
                // Optionally delete the meta if you want to reset
                delete_post_meta($id, 'fr_reviewed');
                continue;
            }

            // Otherwise, this post is stale
            $filtered[] = $id;
        }


        update_option(FR_CACHE_OPTION, array('timestamp' => time(), 'post_ids' => $filtered), false);

        if (! empty($settings['email_notify']) && ! empty($filtered)) {
            self::send_email_digest($filtered, $settings);
        }

        error_log("checked stale posts");
    }

    public static function remove_reviewed_content()
    {
        // remove the reviewved content if it's not pined
        $args = array(
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_fr_reviewed',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => '_fr_pined',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $q = new WP_Query($args);
        $reviewed_unpined_posts = $q->posts ? $q->posts : array();

        foreach ($reviewed_unpined_posts as $post_id) {
            delete_post_meta($post_id, '_fr_reviewed');
        }

        //romove from the cache
        $cache = get_option(FR_CACHE_OPTION);
        $stale_post_ids = isset($cache['post_ids']) ? array_unique($cache['post_ids']) : array();
        $updated_stale_post_ids = array_diff($stale_post_ids, $reviewed_unpined_posts);
        update_option(FR_CACHE_OPTION, array('timestamp' => time(), 'post_ids' => $updated_stale_post_ids), false);

        error_log("removed reviewed content");
    }

    public static function send_email_digest($post_ids, $settings)
    {
        error_log("send admin notify email");
    }
}

/* ensure the scheduled hook` is always available */
add_action('fr_check_event', array('FR_Cron', 'check_stale_posts')); //a custom action
add_action('fr_clear_reviewed_event', array('FR_Cron', 'remove_reviewed_content')); //
