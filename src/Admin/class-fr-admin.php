<?php

if (!defined('ABSPATH')) exit;

use Fresh\Reminder\Utils\Logger;

class FR_admin
{
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu')); //add page for admin page
        add_action('wp_dashboard_setup', array(__CLASS__, 'register_dashboard_widget'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_action('wp_ajax_fr_mark_reviewed', array(__CLASS__, 'ajax_mark_reviewed'));
        add_action('wp_ajax_fr_unmark_reviewed', array(__CLASS__, 'ajax_unmark_reviewed'));
        add_action('wp_ajax_fr_mark_pined', array(__CLASS__, 'ajax_mark_pined'));
        add_action('wp_ajax_fr_unmark_pined', array(__CLASS__, 'ajax_unmark_pined'));
        // ensure cron handler attached when admin loads
        add_action('fr_check_event', array('FR_Cron', 'check_stale_posts'));
        add_action('fr_clear_reviewed_event', array('FR_Cron', 'remove_reviewed_content'));
        // do_action('fr_check_event');
        // do_action('fr_clear_reviewed_event');
    }

    public static function add_admin_menu()
    {

        //base 64 svg icon
        $icon_base64_icon = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjI4IiBoZWlnaHQ9IjIzMCIgdmlld0JveD0iMCAwIDIyOCAyMzAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xNi45OTM0IDU0Ljg4NDZMMjIuMDMxOCA1MC45MzlDNDYuMTM4OSAzMi4wMzY2IDc4LjU2NjQgMzQuMjM1IDg5LjQ4NzUgNTUuNTE4MkM5NS42MTk4IDY3LjQ3OTggOTMuMTkxOCA3NC4wODQ2IDgyLjY2NDIgNzQuMDg0NkM2MC44Nzk1IDc0LjA4NDYgNDYuMDE0MSAxMDMuNjUzIDUzLjQ1MTYgMTMyLjE4NEM2NC4xNjE2IDE3My4yNTMgMTE5LjMxNCAxOTMuNDg5IDE1My4wOTUgMTY4LjczMUMxNjAuOTY0IDE2Mi45NjEgMTYwLjc5MiAxNjIuMDY5IDE1MS45ODIgMTYyLjkwNEMxMzUuNDI3IDE2NC40NDkgMTIwLjYxOSAxNTQuNDA4IDExNC42NDEgMTM3LjU2OUMxMTAuMTY5IDEyNC45NTUgMTEwLjkxNyAxMTQuNTI5IDExNy41ODcgOTYuNDkxQzEyMy4zMjYgODAuOTY3OCAxMjMuNzk2IDc1Ljk3NTggMTIwLjU3MSA2NC40MTc0QzExNC43ODUgNDMuNjIzOCA5OC4xOTE4IDIyLjI2MzggNzEuNjk1MSA3Ljg0NDY0QzEwNy4xMDcgLTUuNDAzMzYgMTQzLjY2MSAtMS4yNDY1NiAxNzEuODU3IDE1LjYxMUMyMzcuODU0IDU1LjA2NyAyNDcuMTM0IDE0Ny4zMDQgMTkwLjM1IDE5OS4yNzhDMTIwLjI5MyAyNjMuMzk3IDguODE3IDIxOS4xMzEgMC4zNzE4MzMgMTIzLjg1MUMtMS42MDUxIDEwMS41MzEgNC40Njk2NiA3My41NjYyIDE0LjU4NDcgNTguNDg0NkwxNi45OTM0IDU0Ljg4NDZaTTEzMi42ODMgOTcuNTI3OEMxMTQuMzUzIDExNS40NyAxMTguODkyIDE0NC43NDEgMTQxLjA4IDE1MS42OTFDMTQzLjAzNyAxNTIuMzA1IDE0Ni4xOTUgMTUyLjgwNSAxNDguMTE0IDE1Mi44MDVDMTUwLjAzNCAxNTIuODA1IDE1My45NDkgMTUxLjkxMiAxNTYuODE4IDE1MC44MDhDMTcwLjMxMiAxNDUuNjYyIDE3Ni4yMDQgMTMxLjM5NyAxNzMuMDA4IDExMS42NTlDMTY5LjQzOCA4OS43MzI2IDE3MC4yMTYgODUuNDc5OCAxNzguNDY5IDgxLjczNThDMTgyLjUzOCA3OS44ODMgMTgyLjUzOCA3OS44ODMgMTc3LjEzNSA3OS44NjM4QzE2My4xMjQgNzkuODA2MiAxNDIuMzU2IDg4LjA2MjIgMTMyLjY4MyA5Ny41Mjc4WiIgZmlsbD0iIzlDQTJBNyIvPgo8L3N2Zz4K";
        //add admin pages
        add_menu_page(
            __('Home', 'fresh-reminder'),
            __('Fresh Reminder', 'fresh-reminder'),
            'manage_options',
            'fr-home',
            array(__CLASS__, 'home_page'),
            $icon_base64_icon,
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

    public static function home_page()
    {
        include_once __DIR__ . '/Pages/HomePage.php';
    }

    public static function enqueue_assets($hook)
    {
        if ('toplevel_page_fr-home' === $hook || 'fresh-reminder_page_fr-checkbucket' === $hook || 'fresh-reminder_page_fr-settings' === $hook) {
            wp_enqueue_script('fr-admin-js', FR_PLUGIN_URL . '/assets/js/admin/admin.js', array('jquery'), FR_VERSION, true);
            wp_localize_script('fr-admin-js', 'fr_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('fr_nonce'),
            ));
            wp_localize_script('fr-admin-js', 'fr_admin_urls', array(
                'home_page'        => admin_url('admin.php?page=fr-home'),
                'check_bucket_page' => admin_url('admin.php?page=fr-checkbucket'),
                'settings_page'    => admin_url('admin.php?page=fr-settings'),
                'help_page'    => 'https://github.com/hasunB/fresh-reminder/discussions',
            ));

            // Enqueue Chart.js library
            wp_enqueue_script('chartjs', FR_PLUGIN_URL . 'assets/js/cdn/chart-js/chart.min.js', array(),'4.5.1', true);
            wp_enqueue_script('fr-charts-js', FR_PLUGIN_URL . '/assets/js/admin/charts.js', array('jquery'), FR_VERSION, true);
            wp_enqueue_script('fr-settings-js', FR_PLUGIN_URL . '/assets/js/admin/settings.js', array('jquery'), FR_VERSION, true);

            $cache = get_option(FR_CACHE_OPTION);
            $stale_post_ids = isset($cache['post_ids']) ? array_unique($cache['post_ids']) : array();

            $total_stale_posts = count($stale_post_ids);
            $reviewed_posts_count = 0;
            $unreviewed_posts_count = 0;

            if ($total_stale_posts > 0) {
                foreach ($stale_post_ids as $post_id) {
                    if (get_post_meta($post_id, '_fr_reviewed', true)) {
                        $reviewed_posts_count++;
                    } else {
                        $unreviewed_posts_count++;
                    }
                }
            }

            $chartjs_data = array(

                'reviewed' => $reviewed_posts_count,
                'unreviewed' => $unreviewed_posts_count,
            );

            wp_localize_script('fr-charts-js', 'fr_chart_data', $chartjs_data);

            // Enqueue CSS/JS
            wp_enqueue_style('fr-admin-css', FR_PLUGIN_URL . '/assets/css/admin.css', array(), FR_VERSION);
            wp_enqueue_style('fr-settings-css', FR_PLUGIN_URL . '/assets/css/settings.css', array(), FR_VERSION);
            // Enqueue Bootstrap CSS/JS
            wp_enqueue_style('bootstrap-css', FR_PLUGIN_URL . '/assets/css/cdn/bootstrap/bootstrap.min.css', array(), '5.3.0');
            //add font-awesome css
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css', array(), '7.0.1');
            wp_enqueue_script('popper-js', FR_PLUGIN_URL . '/assets/js/cdn/popper/popper.min.js', array(), '2.11.8', true);
            wp_enqueue_script('bootstrap-js', FR_PLUGIN_URL . '/assets/js/cdn/bootstrap/bootstrap.min.js', array('popper-js'), '5.3.0', true);
        }
    }

    public static function settings_page()
    {
        include_once __DIR__ . '/Pages/SettingsPage.php';
    }

    public static function checkbucket_page()
    {
        include_once __DIR__ . '/Pages/CheckBucketPage.php';
    }

    public static function register_dashboard_widget()
    {
        wp_add_dashboard_widget('fr_dashboard_widget', __('Freshness Tracking', 'fresh-reminder'), array(__CLASS__, 'dashboard_widget_render'));
    }

    public static function dashboard_widget_render()
    {
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
?>
        <div class="fr-dashboard-widget-content">
            <div class="w-100 h-100 p-3">
                <!-- content-box -->
                <div class="w-100 h-100 chart-content-box" style="display: none;">
                    <div class="pie-chart">
                        <canvas id="fr_piechart_canvas"></canvas>
                    </div>
                    <div class="w-100 chart-legend">
                        <div class="w-50 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage"><?php echo esc_html($reviewed_posts_count); ?></span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <div class="legend-indicator indicator-reviewed"></div>
                                    <span class="legend-label">Reviewed</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-50 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage"><?php echo esc_html($unreviewed_posts_count); ?></span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <div class="legend-indicator indicator-unreviewed"></div>
                                    <span class="legend-label">Unreviewed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-100 chart-legend">
                        <div class="w-100 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage"><?php echo esc_html($total_stale_posts); ?></span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <span class="legend-label">Total Stale Posts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- no-content-box -->
                <div class="w-100 no-chart-content-box" style="display: none;">
                    <div></div>
                    <h5>No Data Found</h5>
                </div>
            </div>


            <?php if ($unreviewed_posts_count > 0) : ?>
                <div class="admin-widget-btn-box">
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fr-home')); ?>" class="theme-btn bg-post"><?php esc_html_e('Review Stale Posts', 'fresh-reminder'); ?></a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
<?php
    }

    // AJAX Handlers
    // Mark post as reviewed
    public static function ajax_mark_reviewed()
    {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        update_post_meta($post_id, '_fr_reviewed', time());

        wp_send_json_success(array('post_id' => $post_id));
    }

    // Unmark post as reviewed
    public static function ajax_unmark_reviewed()
    {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        delete_post_meta($post_id, '_fr_reviewed');
        wp_send_json_success(array('post_id' => $post_id));
    }

    // Mark post as pined
    public static function ajax_mark_pined()
    {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        update_post_meta($post_id, '_fr_pined', true);

        wp_send_json_success(array('post_id' => $post_id));
    }

    // Unmark post as pined
    public static function ajax_unmark_pined()
    {
        check_ajax_referer('fr_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        delete_post_meta($post_id, '_fr_pined');
        wp_send_json_success(array('post_id' => $post_id));
    }
}

?>