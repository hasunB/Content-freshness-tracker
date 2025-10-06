<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'fr_settings' );
delete_option( 'fr_stale_posts_cache' );

// Remove review meta
$posts = get_posts( array(
    'post_type'   => 'any',
    'meta_key'    => '_fr_reviewed',
    'numberposts' => -1,
    'fields'      => 'ids',
) );

if ( $posts ) {
    foreach ( $posts as $id ) {
        delete_post_meta( $id, '_fr_reviewed' );
    }
}
