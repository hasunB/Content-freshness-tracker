<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'cft_settings' );
delete_option( 'cft_stale_posts_cache' );

// Remove review meta
$posts = get_posts( array(
    'post_type'   => 'any',
    'meta_key'    => '_cft_reviewed',
    'numberposts' => -1,
    'fields'      => 'ids',
) );

if ( $posts ) {
    foreach ( $posts as $id ) {
        delete_post_meta( $id, '_cft_reviewed' );
    }
}
