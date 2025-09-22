<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class CFT_Cron {

    public static function get_default(){
        return array(
            'months'      => 12,
            'post_types'  => array( 'post' ),
            'schedule'    => 'daily', // 'hourly', 'twicedaily', 'daily'
            'email_notify'=> 0,
            'roles'       => array( 'administrator', 'editor' ),
        );
    }

    public static function activate(){
        error_log("plugin activated");
        $settings = get_option( CFT_OPTION_NAME, self::get_defaults() );
        $schedule = ! empty( $settings['schedule'] ) ? $settings['schedule'] : 'daily';
        if ( ! wp_next_scheduled( 'cft_check_event' ) ) {
            wp_schedule_event( time(), $schedule, 'cft_check_event' );
        }
    }

    public static function deactivate(){
        error_log("plugin deactivated");
        wp_clear_scheduled_hook( 'cft_check_event' );
    }

    public static function check_stale_posts() {
        $settings = get_option( CFT_OPTION_NAME, self::get_defaults() );
        $months   = max( 1, intval( $settings['months'] ) );
        $post_types = ! empty( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );
        $before = date( 'Y-m-d H:i:s', strtotime( "-{$months} months" ) );

        $args = array(
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'date_query'     => array( array( 'before' => $before ) ),
            'fields'         => 'ids',
            'posts_per_page' => -1,
        );

        //fetch data from DB
        $q = new WP_Query( $args );
        $ids = $q->posts ? $q->posts : array();

        // Filter out posts that were reviewed AFTER last modification
        $filtered = array();
        foreach ( $ids as $id ) {
            $reviewed = get_post_meta( $id, '_cft_reviewed', true );
            $post_modified = get_post_field( 'post_modified', $id );
            if ( $reviewed ) {
                if ( intval( $reviewed ) >= strtotime( $post_modified ) ) {
                    continue; // already reviewed after last modification
                }
            }
            $filtered[] = $id;
        }

        update_option( CFT_CACHE_OPTION, array( 'timestamp' => time(), 'post_ids' => $filtered ), false );

        if ( ! empty( $settings['email_notify'] ) && ! empty( $filtered ) ) {
            self::send_email_digest( $filtered, $settings );
        }

        
    }

    public static function send_email_digest( $post_ids, $settings ) {
        error_log("send admin notify email");
    }
    
}

/* ensure the scheduled hook is always available */
add_action( 'cft_check_event', array( 'CFT_Cron', 'check_stale_posts' ) ); //a custom action

?>