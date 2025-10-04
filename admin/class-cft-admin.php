<?php

if (!defined('ABSPATH')) exit;

class CFT_admin {
    public static function init() {
        add_action( 'admin_menu', array(__CLASS__, 'add_admin_menu')); //add page for admin page
        add_action( 'wp_dashboard_setup', array(__CLASS__, 'register_dashboard_widget'));
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_cft_mark_reviewed', array( __CLASS__, 'ajax_mark_reviewed' ) );
        add_action( 'wp_ajax_cft_unmark_reviewed', array( __CLASS__, 'ajax_unmark_reviewed' ) );
        // ensure cron handler attached when admin loads
        add_action( 'cft_check_event', array( 'CFT_Cron', 'check_stale_posts' ) );
        do_action('cft_check_event');
    }

    public static function add_admin_menu() {

        //add admin pages
        add_menu_page(
            __('Home', 'content-freshness-tracker'),
            __('Content Freshness', 'content-freshness-tracker'),
            'manage_options',
            'cft-home',
            array(__CLASS__, 'home_page'),
            'dashicons-tide',
            25
        );

        //add submenu for home
        add_submenu_page(
            'cft-home',
            __('Home', 'content-freshness-tracker'),
            __('Home', 'content-freshness-tracker'),
            'manage_options',
            'cft-home',
            array(__CLASS__, 'home_page')
        );

        //add submenu for settings
        add_submenu_page(
            'cft-home',
            __('Settings', 'content-freshness-tracker'),
            __('Settings', 'content-freshness-tracker'),
            'manage_options',
            'cft-settings',
            array(__CLASS__, 'settings_page')
        );
    }

    public static function home_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            echo '<p>' . esc_html__( 'No permission to view.', 'content-freshness-tracker' ) . '</p>';
            return;
        }

        $defaults = CFT_Cron::get_default();
        $settings = get_option(CFT_OPTION_NAME, $defaults);

        $post_types = isset($_POST['post_types']) ? array_map('sanitize_text_field', array_keys($_POST['post_types'])) : array('post');

        $args = array(
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'posts_per_page' => -1,
        );
        
        //fetch data from DB
        $q = new WP_Query( $args );
        $ids = $q->posts ? $q->posts : array();

        //total post count
        $total_posts = count( $ids );    

        $cache = get_option( CFT_CACHE_OPTION );
        $post_ids = isset( $cache['post_ids'] ) ? array_unique($cache['post_ids']) : array();

        $posts_data = array();
        foreach ( $post_ids as $post_id ) {
            $post = get_post( $post_id );
            if ( $post ) {
                $posts_data[] = (object) array(
                    'ID'                => $post->ID,
                    'post_title'        => $post->post_title,
                    'post_author_id'    => $post->post_author,
                    'post_author_name'  => get_the_author_meta( 'display_name', $post->post_author ),
                    'post_type'         => $post->post_type,
                    'post_date'         => $post->post_date,
                    'post_modified'     => $post->post_modified,
                    'reviewed'          => get_post_meta( $post->ID, '_cft_reviewed', true ) ? true : false,
                    'edit_link'         => get_edit_post_link( $post->ID ),
                );
            }
        }

        $total_stale_posts = count( $posts_data );
        $reviewed_posts_count = count( array_filter( $posts_data, function( $post ) { return $post->reviewed; } ) );
        $unreviewed_posts_count = $total_stale_posts - $reviewed_posts_count;
        


    ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Freshness Tracking', 'content-freshness-tracker' ); ?></h1>
            
            <!-- Stats Cards -->
            <div class="row gx-3 my-0">
                <!-- Total Stale Posts -->
                <div class="col-md-4">
                    <div class="card shadow-sm stat-card h-80">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-0 fw-semibold small">Total Stale Posts</h6>
                                </div>
                            </div>
                            <h2 class="display-4 fw-light mb-0"><?php echo esc_html( $total_stale_posts ); ?></h2>
                        </div>
                    </div>
                </div>
                <!-- Reviewed Posts -->
                <div class="col-md-4">
                    <div class="card shadow-sm stat-card h-80">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-0 fw-semibold small">Reviewed Posts</h6>
                                </div>
                            </div>
                            <h2 class="display-4 fw-light mb-0" id="reviewedCount"><?php echo esc_html( $reviewed_posts_count ); ?></h2>
                        </div>
                    </div>
                </div>
                <!-- Unreviewed Posts -->
                <div class="col-md-4">
                    <div class="card shadow-sm stat-card h-80">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-0 fw-semibold small">Unreviewed Posts</h6>
                                </div>
                            </div>
                            <h2 class="display-4 fw-light mb-0" id="unreviewedCount"><?php echo esc_html( $unreviewed_posts_count ); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts Table -->
            <div class="shadow-sm mt-4 w-100 px-4 py-3 my-card">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-list text-secondary me-2"></i>
                                Stale Posts
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group float-md-end mt-2 mt-md-0" role="group">
                                <button type="button" class="btn btn-sm btn-secondary active" data-filter="all">All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="posts">Posts</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="pages">Pages</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="products">Products</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="media">Media</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="unreviewed">Unreviewed</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="reviewed">Reviewed</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold text-start">Title</th>
                                    <th class="fw-semibold text-center">Author</th>
                                    <th class="fw-semibold text-center">Type</th>
                                    <th class="fw-semibold text-center">Published Date</th>
                                    <th class="fw-semibold text-center">Last Modified Date</th>
                                    <th class="fw-semibold text-center">Status</th>
                                    <th class="fw-semibold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $posts_data as $post ) : ?>
                                    <tr data-post-id="<?php echo esc_attr( $post->ID ); ?>" data-status="<?php echo $post->reviewed ? 'reviewed' : 'unreviewed'; ?>">
                                        <td>
                                            <a href="<?php echo esc_url( $post->edit_link ); ?>" class="post-title"><?php echo esc_html( $post->post_title ); ?></a>
                                        </td>
                                        <td class="text-muted text-center"><?php echo esc_html( $post->post_author_name ); ?></td>
                                        <td class="align-middle text-center">
                                            <?php if ( $post->post_type == "post" ) : ?>
                                                <span class="badge bg-success"><?php esc_html_e( 'Post', 'content-freshness-tracker' ); ?></span>
                                            <?php elseif ( $post->post_type == "page" ) :?>
                                                <span class="badge bg-primary"><?php esc_html_e( 'Page', 'content-freshness-tracker' ); ?></span>
                                            <?php elseif ( $post->post_type == "product" ): ?>
                                                <span class="badge bg-danger"><?php esc_html_e( 'Product', 'content-freshness-tracker' ); ?></span>
                                            <?php else : ?>
                                                <span class="badge bg-dark"><?php esc_html_e( 'Media', 'content-freshness-tracker' ); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small text-center"><?php echo esc_html( $post->post_date ); ?></td>
                                        <td class="text-muted small text-center"><?php echo esc_html( $post->post_modified ); ?></td>
                                        <td class="align-middle text-center">
                                            <?php if ( $post->reviewed ) : ?>
                                                <span class="badge bg-success-subtle text-success status-badge"><?php esc_html_e( 'Reviewed', 'content-freshness-tracker' ); ?></span>
                                            <?php else : ?>
                                                <span class="badge bg-danger-subtle text-danger status-badge"><?php esc_html_e( 'Stale', 'content-freshness-tracker' ); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <?php if ( $post->reviewed ) : ?>
                                                <button class="btn btn-sm btn-warning cft-mark-unreview" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                                    <i class="fas fa-undo me-1"></i>
                                                    <?php esc_html_e( 'Unmark Reviewed', 'content-freshness-tracker' ); ?>
                                                </button>
                                            <?php else : ?>
                                                <button class="btn btn-sm btn-primary cft-mark-review" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                                    <i class="fas fa-check me-1"></i>
                                                    <?php esc_html_e( 'Mark Reviewed', 'content-freshness-tracker' ); ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public static function enqueue_assets($hook) {
        if ('index.php' === $hook || 'toplevel_page_cft-home' === $hook || 'settings_page_cft-settings' === $hook) {
            wp_enqueue_script('cft-admin-js', CFT_PLUGIN_URL . '/assets/js/admin.js', array('jquery'), CFT_VERSION, true);
            wp_localize_script('cft-admin-js', 'cft_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('cft_nonce'),
            ));
            wp_enqueue_style('cft-admin-css', CFT_PLUGIN_URL . '/assets/css/admin.css', array(), CFT_VERSION);
            // Enqueue Bootstrap CSS/JS
            wp_enqueue_style('bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css', array(), '5.3.0');
            //add font-awesome css
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0-beta3');
            wp_enqueue_script('popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js', array(), '2.11.8', true);
            wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js', array('popper-js'), '5.3.0', true);
        }
    }

    public static function settings_page() {

        if (! current_user_can('manage_options')) return;

        $defaults = CFT_Cron::get_default();
        $settings = get_option(CFT_OPTION_NAME, $defaults);

        if (isset($_POST['cft_save']) && check_admin_referer('cft_save_settings', 'cft_nonce')) {
            $months = isset($_POST['months']) ? absint($_POST['months']) : $defaults['months'];
            $post_types = isset($_POST['post_types']) ? array_map('sanitize_text_field', array_keys($_POST['post_types'])) : array('post');
            $schedule = isset($_POST['schedule']) && in_array($_POST['schedule'], array('hourly', 'twicedaily', 'daily')) ? $_POST['schedule'] : 'daily';
            $email_notify = isset($_POST['email_notify']) ? 1 : 0;
            $roles = isset($_POST['roles']) ? array_map('sanitize_text_field', array_keys($_POST['roles'])) : $defaults['roles'];

            $new = compact('months', 'post_types', 'schedule', 'email_notify', 'roles');
            update_option(CFT_OPTION_NAME, $new);

            // reschedule
            wp_clear_scheduled_hook('cft_check_event');
            wp_schedule_event(time(), $schedule, 'cft_check_event');

            echo '<div class="updated"><p>' . esc_html__('Settings saved', 'content-freshness-tracker') . '</p></div>';
            $settings = $new;
        }

    ?>
        <div class="wrap">
            <h1><?php esc_html_e('Content Freshness Tracker', 'content-freshness-tracker'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('cft_save_settings', 'cft_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="months"><?php esc_html_e('Stale after (months)', 'content-freshness-tracker'); ?></label></th>
                        <td><input type="number" name="months" id="months" value="<?php echo esc_attr($settings['months']); ?>" min="1" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Post types', 'content-freshness-tracker'); ?></th>
                        <td>
                            <?php
                            $types = get_post_types(array('public' => true), 'objects');
                            foreach ($types as $type) {
                                $checked = in_array($type->name, $settings['post_types']) ? 'checked' : '';
                                echo '<label style="display:block"><input type="checkbox" name="post_types[' . esc_attr($type->name) . ']" value="1" ' . $checked . ' /> ' . esc_html($type->labels->singular_name) . '</label>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Schedule', 'content-freshness-tracker'); ?></th>
                        <td>
                            <label><input type="radio" name="schedule" value="hourly" <?php checked($settings['schedule'], 'hourly'); ?> /> Hourly</label><br />
                            <label><input type="radio" name="schedule" value="twicedaily" <?php checked($settings['schedule'], 'twicedaily'); ?> /> Twice daily</label><br />
                            <label><input type="radio" name="schedule" value="daily" <?php checked($settings['schedule'], 'daily'); ?> /> Daily</label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Email digest', 'content-freshness-tracker'); ?></th>
                        <td><label><input type="checkbox" name="email_notify" value="1" <?php checked($settings['email_notify'], 1); ?> /> <?php esc_html_e('Send digest to selected roles', 'content-freshness-tracker'); ?></label></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Notify roles', 'content-freshness-tracker'); ?></th>
                        <td>
                            <?php
                            $roles = wp_roles()->roles;
                            foreach ($roles as $role_key => $role) {
                                $checked = in_array($role_key, $settings['roles']) ? 'checked' : '';
                                echo '<label style="display:block"><input type="checkbox" name="roles[' . esc_attr($role_key) . ']" value="1" ' . $checked . ' /> ' . esc_html($role['name']) . '</label>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="cft_save" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'content-freshness-tracker'); ?>" /></p>
            </form>
        </div>
    <?php
    }

    public static function register_dashboard_widget() {
        wp_add_dashboard_widget('cft_dashboard_widget', __('Content Freshness Tracker', 'content-freshness-tracker'), array(__CLASS__, 'dashboard_widget_render'));
    }

    public static function dashboard_widget_render() {
        //checking the admin permissions
        if (! current_user_can('edit_posts')) {
            echo '<p>' . esc_html__('No permission to view.', 'content-freshness-tracker') . '</p>';
            return;
        }

        $cache = get_option(CFT_CACHE_OPTION);
        $stale_post_ids = isset($cache['post_ids']) ? array_unique($cache['post_ids']) : array();

        $reviewed_posts_count = 0;
        $unreviewed_posts_count = 0;

        foreach ($stale_post_ids as $post_id) {
            if (get_post_meta($post_id, '_cft_reviewed', true)) {
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
                    chartArea: {top:30, bottom:70}
                };

                var chart = new google.visualization.PieChart(document.getElementById("cft_piechart"));
                chart.draw(data, options);
            }
        ');
        ?>
        <div class="cft-dashboard-widget-content">
            <div id="cft_piechart" style="width: 100%; height: 250px; margin-bottom: 0px;"></div>
            <div class="container text-center">
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-text"><?php esc_html_e('Stale Posts', 'content-freshness-tracker'); ?></p>
                                <h5 class="card-title"><?php echo esc_html($total_stale_posts); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-text"><?php esc_html_e('Reviewed Posts', 'content-freshness-tracker'); ?></p>
                                <h5 class="card-title"><?php echo esc_html($reviewed_posts_count); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-text"><?php esc_html_e('Unreviewed Posts', 'content-freshness-tracker'); ?></p>
                                <h5 class="card-title"><?php echo esc_html($unreviewed_posts_count); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($unreviewed_posts_count > 0) : ?>
                <p class="mt-3 text-center"><a href="<?php echo esc_url(admin_url('admin.php?page=cft-home')); ?>" class="btn btn-primary"><?php esc_html_e('Review Stale Posts', 'content-freshness-tracker'); ?></a></p>
            <?php endif; ?>
        </div>
    <?php
    }

    public static function ajax_mark_reviewed() {
        check_ajax_referer('cft_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');

        update_post_meta($post_id, '_cft_reviewed', time());
        
        // remove from cache so widget updates quickly
        $cache = get_option(CFT_CACHE_OPTION);
        if ($cache && ! empty($cache['post_ids'])) {
            $cache['post_ids'] = array_values(array_diff($cache['post_ids'], array($post_id)));
            update_option(CFT_CACHE_OPTION, $cache);
        }
        
        wp_send_json_success(array('post_id' => $post_id));
        error_log('ajax_mark_reviewed'.$post_id);
    }

    public static function ajax_unmark_reviewed() {
        check_ajax_referer('cft_nonce', 'nonce');
        if (! current_user_can('edit_posts')) wp_send_json_error('no_permission');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (! $post_id) wp_send_json_error('invalid_id');
        
        delete_post_meta($post_id, '_cft_reviewed');
        wp_send_json_success(array('post_id' => $post_id));
        error_log('ajax_unmark_reviewed');
    }
}

?>