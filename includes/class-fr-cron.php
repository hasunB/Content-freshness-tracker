<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class FR_Cron {

    public static function get_default(){
        return array(
            'months'      => 1,
            'post_types'  => array( 'post' ),
            'schedule'    => 'daily', // 'hourly', 'twicedaily', 'daily'
            'email_notify'=> 0,
            'roles'       => array( 'administrator', 'editor' ),
        );
    }

    public static function activate(){
        error_log("plugin activated");
        $settings = get_option( FR_OPTION_NAME, self::get_default() );
        $schedule = ! empty( $settings['schedule'] ) ? $settings['schedule'] : 'daily';
        if ( ! wp_next_scheduled( 'fr_check_event' ) ) {
            wp_schedule_event( time(), $schedule, 'fr_check_event' );
        }
    }

    public static function deactivate(){
        error_log("plugin deactivated");
        wp_clear_scheduled_hook( 'fr_check_event' );
    }

    public static function check_stale_posts() {
        $settings = get_option( FR_OPTION_NAME, self::get_default() );
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
            $reviewed = get_post_meta( $id, 'fr_reviewed', true );
            $post_modified = get_post_field( 'post_modified', $id );
            if ( $reviewed ) {
                if ( intval( $reviewed ) >= strtotime( $post_modified ) ) {
                    $filtered[] = $id;
                    error_log('Stale post Reviewd found ID : '.$id);
                }
            }
            $filtered[] = $id;
            error_log('Stale post found ID : '.$id);
        }
        
        update_option( FR_CACHE_OPTION, array( 'timestamp' => time(), 'post_ids' => $filtered ), false );
        
        if ( ! empty( $settings['email_notify'] ) && ! empty( $filtered ) ) {
            self::send_email_digest( $filtered, $settings );
        }
        
        error_log("checked stale posts");
        
    }

    public static function send_email_digest( $post_ids, $settings ) {
        error_log("send admin notify email");
    }
    
}

/* ensure the scheduled hook is always available */
add_action( 'fr_check_event', array( 'FR_Cron', 'check_stale_posts' ) ); //a custom action

?>