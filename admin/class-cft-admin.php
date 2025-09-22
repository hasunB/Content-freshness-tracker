<?php

if (!defined( 'ABSPATH' )) exit;

class CFT_admin {
    public static function init() {
        add_action( 'admin_menu', array(__CLASS__,'add_admin_menu' )); //add page for admin page
        add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_cft_mark_reviewed', array( __CLASS__, 'ajax_mark_reviewed' ) );
        add_action( 'wp_ajax_cft_unmark_reviewed', array( __CLASS__, 'ajax_unmark_reviewed' ) );
        // ensure cron handler attached when admin loads
        add_action( 'cft_check_event', array( 'CFT_Cron', 'check_stale_posts' ) );
    }

    public static function add_admin_menu(){

        //add admin pages
        add_menu_page(
            __('Home', 'content-freshness-tracker'),
            __('Content Freshness', 'content-freshness-tracker'),
            'manage_options',
            'cft-home',
            array(__CLASS__,'home_page'),
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
        ?>
            <h1>Home Page</h1>
        <?php
    }

    public static function settings_page() {

        if ( ! current_user_can( 'manage_options' ) ) return;

        $defaults = CFT_Cron::get_default();
        $settings = get_option( 'CFT_OPTION_NAME', $defaults); 

        if ( isset( $_POST['cft_save'] ) && check_admin_referer( 'cft_save_settings', 'cft_nonce' ) ) {
            $months = isset( $_POST['months'] ) ? absint( $_POST['months'] ) : $defaults['months'];
            $post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', array_keys( $_POST['post_types'] ) ) : array( 'post' );
            $schedule = isset( $_POST['schedule'] ) && in_array( $_POST['schedule'], array( 'hourly','twicedaily','daily' ) ) ? $_POST['schedule'] : 'daily';
            $email_notify = isset( $_POST['email_notify'] ) ? 1 : 0;
            $roles = isset( $_POST['roles'] ) ? array_map( 'sanitize_text_field', array_keys( $_POST['roles'] ) ) : $defaults['roles'];

            $new = compact( 'months','post_types','schedule','email_notify','roles' );
            update_option( CFT_OPTION_NAME, $new );

            // reschedule
            wp_clear_scheduled_hook( 'cft_check_event' );
            wp_schedule_event( time(), $schedule, 'cft_check_event' );

            echo '<div class="updated"><p>' . esc_html__( 'Settings saved', 'content-freshness-tracker' ) . '</p></div>';
            $settings = $new;
        }

        ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Content Freshness Tracker', 'content-freshness-tracker' ); ?></h1>
                <form method="post">
                    <?php wp_nonce_field( 'cft_save_settings', 'cft_nonce' ); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="months"><?php esc_html_e( 'Stale after (months)', 'content-freshness-tracker' ); ?></label></th>
                            <td><input type="number" name="months" id="months" value="<?php echo esc_attr( $settings['months'] ); ?>" min="1" class="small-text" /></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Post types', 'content-freshness-tracker' ); ?></th>
                            <td>
                                <?php
                                    $types = get_post_types( array( 'public' => true ), 'objects' );
                                    foreach ( $types as $type ) {
                                        $checked = in_array( $type->name, $settings['post_types'] ) ? 'checked' : '';
                                        echo '<label style="display:block"><input type="checkbox" name="post_types['.esc_attr($type->name).']" value="1" '.$checked.' /> '.esc_html($type->labels->singular_name).'</label>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Schedule', 'content-freshness-tracker' ); ?></th>
                            <td>
                                <label><input type="radio" name="schedule" value="hourly" <?php checked( $settings['schedule'], 'hourly' ); ?> /> Hourly</label><br/>
                                <label><input type="radio" name="schedule" value="twicedaily" <?php checked( $settings['schedule'], 'twicedaily' ); ?> /> Twice daily</label><br/>
                                <label><input type="radio" name="schedule" value="daily" <?php checked( $settings['schedule'], 'daily' ); ?> /> Daily</label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Email digest', 'content-freshness-tracker' ); ?></th>
                            <td><label><input type="checkbox" name="email_notify" value="1" <?php checked( $settings['email_notify'], 1 ); ?> /> <?php esc_html_e( 'Send digest to selected roles', 'content-freshness-tracker' ); ?></label></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Notify roles', 'content-freshness-tracker' ); ?></th>
                            <td>
                                <?php
                                    $roles = wp_roles()->roles;
                                    foreach ( $roles as $role_key => $role ) {
                                        $checked = in_array( $role_key, $settings['roles'] ) ? 'checked' : '';
                                        echo '<label style="display:block"><input type="checkbox" name="roles['.esc_attr($role_key).']" value="1" '.$checked.' /> '.esc_html($role['name']).'</label>';
                                    }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="cft_save" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'content-freshness-tracker' ); ?>" /></p>
                </form>
            </div> 
        <?php
    }

    public static function ajax_mark_reviewed() {
        check_ajax_referer( 'cft_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'no_permission' );

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) wp_send_json_error( 'invalid_id' );

        update_post_meta( $post_id, '_cft_reviewed', time() );

        // remove from cache so widget updates quickly
        $cache = get_option( CFT_CACHE_OPTION );
        if ( $cache && ! empty( $cache['post_ids'] ) ) {
            $cache['post_ids'] = array_values( array_diff( $cache['post_ids'], array( $post_id ) ) );
            update_option( CFT_CACHE_OPTION, $cache );
        }
        
        wp_send_json_success( array( 'post_id' => $post_id ) );
    }
    
    public static function ajax_unmark_reviewed() {
        check_ajax_referer( 'cft_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'no_permission' );

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) wp_send_json_error( 'invalid_id' );

        delete_post_meta( $post_id, '_cft_reviewed' );
        wp_send_json_success( array( 'post_id' => $post_id ) );
    }


    
}

?>