<?php

if (!defined('ABSPATH')) exit;

class FR_admin {
    public static function init() {
        add_action( 'admin_menu', array(__CLASS__, 'add_admin_menu')); //add page for admin page
        add_action( 'wp_dashboard_setup', array(__CLASS__, 'register_dashboard_widget'));
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_fr_mark_reviewed', array( __CLASS__, 'ajax_mark_reviewed' ) );
        add_action( 'wp_ajax_fr_unmark_reviewed', array( __CLASS__, 'ajax_unmark_reviewed' ) );
        add_action( 'wp_ajax_fr_mark_pined', array( __CLASS__, 'ajax_mark_pined' ) );
        add_action( 'wp_ajax_fr_unmark_pined', array( __CLASS__, 'ajax_unmark_pined' ) );
        // ensure cron handler attached when admin loads
        add_action( 'fr_check_event', array( 'FR_Cron', 'check_stale_posts' ) );
        do_action('fr_check_event');
    }

    public static function add_admin_menu() {

        //add admin pages
        add_menu_page(
            __('Home', 'fresh-reminder'),
            __('Fresh Reminder', 'fresh-reminder'),
            'manage_options',
            'fr-home',
            array(__CLASS__, 'home_page'),
            'dashicons-tide',
            25
        );

        //add submenu for home
        add_submenu_page(
            'fr-home',
            __('Home', 'fresh-reminder'),
            __('Home', 'fresh-reminder'),
            'manage_options',
            'fr-home',
            array(__CLASS__, 'home_page')
        );

        //add submenu for checkbucket
        add_submenu_page(
            'fr-home',
            __('Check Bucket', 'fresh-reminder'),
            __('Check Bucket', 'fresh-reminder'),
            'manage_options',
            'fr-checkbucket',
            array(__CLASS__, 'checkbucket_page')
        );

        //add submenu for settings
        add_submenu_page(
            'fr-home',
            __('Settings', 'fresh-reminder'),
            __('Settings', 'fresh-reminder'),
            'manage_options',
            'fr-settings',
            array(__CLASS__, 'settings_page')
        );
    }

    public static function home_page() {
        include_once __DIR__ . '/Pages/HomePage.php';
    }

    public static function enqueue_assets($hook) {
        if ('index.php' === $hook || 'toplevel_page_fr-home' === $hook || 'settings_page_fr-settings' === $hook) {
            wp_enqueue_script('fr-admin-js', FR_PLUGIN_URL . '/assets/js/admin.js', array('jquery'), FR_VERSION, true);
            wp_localize_script('fr-admin-js', 'fr_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('fr_nonce'),
            ));
            wp_localize_script('fr-admin-js', 'fr_admin_urls', array(
                'check_bucket_page' => admin_url('admin.php?page=fr-checkbucket'),
                'settings_page'    => admin_url('admin.php?page=fr-settings'),
                'help_page'    => 'https://github.com/hasunB/fresh-reminder/discussions',
            ));
            // Enqueue CSS/JS
            wp_enqueue_style('fr-admin-css', FR_PLUGIN_URL . '/assets/css/admin.css', array(), FR_VERSION);
            // Enqueue Bootstrap CSS/JS
            wp_enqueue_style('bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css', array(), '5.3.0');
            //add font-awesome css
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0-beta3');
            wp_enqueue_script('popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js', array(), '2.11.8', true);
            wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js', array('popper-js'), '5.3.0', true);
        }
    }

    public static function settings_page() {
        include_once __DIR__ . '/Pages/SettingsPage.php';
    }

    public static function checkbucket_page() {
        include_once __DIR__ . '/Pages/CheckBucketPage.php';
    }

    public static function register_dashboard_widget() {
        wp_add_dashboard_widget('fr_dashboard_widget', __('Freshness Tracking', 'fresh-reminder'), array(__CLASS__, 'dashboard_widget_render'));
    }

    public static function dashboard_widget_render() {
        //checking the admin permissions
        if (! current_user_can('edit_posts')) {
            echo '<p>' . esc_html__('No permission to view.', 'fresh-reminder') . '</p>';
            return;
        }

        $cache = get_option(FR_CACHE_OPTION);
        $stale_post_ids = isset($cache['post_ids']) ? array_unique($cache['post_ids']) : array();

        $reviewed_posts_count = 0;
        $unreviewed_posts_count = 0;

        foreach ($stale_post_ids as $post_id) {
            if (get_post_meta($post_id, '_fr_reviewed', true)) {
                $reviewed_posts_count++;
            } else {
                $unreviewed_posts_count++;
            }
        }

        $total_stale_posts = count($stale_post_ids);

        // Prepare data for the chart
        $chart_data = array(
            array('Status', 'Count'),
            array('Reviewed', $reviewed_posts_count),
            array('Unreviewed', $unreviewed_posts_count),
        );

        // Enqueue Google Charts library
        wp_enqueue_script('google-charts', 'https://www.gstatic.com/charts/loader.js', array(), null, true);
        wp_add_inline_script('google-charts', '
            google.charts.load("current", {packages:["corechart"]});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable(' . json_encode($chart_data) . ');

                var options = {
                    pieHole: 0.4,
                    backgroundColor	: "transparent",
                    legend: { alignment: "center", position: "bottom"},
                    chartArea: {top:30, bottom:70},
                    colors: ["#E7C5A7", "#93BBB5"],
                    
                };

                var chart = new google.visualization.PieChart(document.getElementById("fr_piechart"));
                chart.draw(data, options);
            }
        ');
        ?>
        <div class="fr-dashboard-widget-content">
            <div id="fr_piechart" style="width: 100%; height: 250px; margin-bottom: 0px;"></div>
            <div class="container text-center">
                <div class="row">
                    <div class="col">
                        <div class="card theme-outline">
                            <div class="card-body">
                                <p class="card-text"><?php esc_html_e('Stale Posts', 'fresh-reminder'); ?></p>
                                <h5 class="card-title"><?php echo esc_html($total_stale_posts); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card theme-outline">
                            <div class="card-body">
                                <p class="card-text"><?php esc_html_e('Reviewed Posts', 'fresh-reminder'); ?></p>
                                <h5 class="card-title"><?php echo esc_html($reviewed_posts_count); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card theme-outline">
                            <div class="card-body">
                                <p class="card-text"><?php esc_html_e('Unreviewed Posts', 'fresh-reminder'); ?></p>
                                <h5 class="card-title"><?php echo esc_html($unreviewed_posts_count); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($unreviewed_posts_count > 0) : ?>
                <p class="mt-3 text-center"><a href="<?php echo esc_url(admin_url('admin.php?page=fr-home')); ?>" class="theme-btn bg-post"><?php esc_html_e('Review Stale Posts', 'fresh-reminder'); ?></a></p>
            <?php endif; ?>
        </div>
    <?php
    }

    // AJAX Handlers
    // Mark post as reviewed
    public static function ajax_mark_reviewed() {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        update_post_meta($post_id, '_fr_reviewed', time());
        
        // remove from cache so widget updates quickly
        $cache = get_option(FR_CACHE_OPTION);
        if ($cache && ! empty($cache['post_ids'])) {
            $cache['post_ids'] = array_values(array_diff($cache['post_ids'], array($post_id)));
            update_option(FR_CACHE_OPTION, $cache);
        }
        
        wp_send_json_success(array('post_id' => $post_id));
        error_log('ajax_mark_reviewed'.$post_id);
    }

    // Unmark post as reviewed
    public static function ajax_unmark_reviewed() {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');
        
        delete_post_meta($post_id, '_fr_reviewed');
        wp_send_json_success(array('post_id' => $post_id));
        error_log('ajax_unmark_reviewed');
    }

    // Mark post as pined
    public static function ajax_mark_pined() {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        update_post_meta($post_id, '_fr_pined', true);
        
        wp_send_json_success(array('post_id' => $post_id));
        error_log('ajax_mark_pined'.$post_id);
    }

    // Unmark post as pined
    public static function ajax_unmark_pined() {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');
        
        delete_post_meta($post_id, '_fr_pined');
        wp_send_json_success(array('post_id' => $post_id));
        error_log('ajax_unmark_pined');
    }
}

?>