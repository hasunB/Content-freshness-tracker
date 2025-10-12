<?php
if ( ! current_user_can( 'edit_posts' ) ) {
    echo '<p>' . esc_html__( 'No permission to view.', 'fresh-reminder' ) . '</p>';
    return;
}

$defaults = FR_Cron::get_default();
$settings = get_option(FR_OPTION_NAME, $defaults);

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

$cache = get_option( FR_CACHE_OPTION );
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
            'reviewed'          => get_post_meta( $post->ID, '_fr_reviewed', true ) ? true : false,
            'edit_link'         => get_edit_post_link( $post->ID ),
        );
    }
}

$total_stale_posts = count( $posts_data );
$reviewed_posts_count = count( array_filter( $posts_data, function( $post ) { return $post->reviewed; } ) );
$unreviewed_posts_count = $total_stale_posts - $reviewed_posts_count;

?>
<div class="theme-container">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container-fluid d-flex align-items-center justify-content-center">
            <div class="col-9">
                <div class="d-flex align-items-center navbar-action-gap">
                    <div class="logo">FR</div>
                    <div class="theme-search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control" placeholder="Search your content......">
                    </div>
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end navbar-action-gap">
                <div class="d-flex gap-3">
                    <button class="theme-action-btn rotate-45"><i class="fas fa-thumbtack"></i></button>
                    <button class="theme-action-btn"><i class="fas fa-cog"></i></button>
                    <button class="theme-action-btn"><i class="fas fa-question"></i></button>
                </div>
                <div class="logo">HB</div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid d-flex">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Hero Section -->
            <div class="theme-banner widget-skin"></div>
            <div class="spliter"></div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card widget-skin">
                        <div class="stats-icon-box stats-post"></div>
                        <div class="stats-info-box">
                            <span class="stats-number">2/8 reviewed</span>
                            <span class="stats-label">Posts</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                     <div class="stats-card widget-skin">
                        <div class="stats-icon-box stats-page"></div>
                        <div class="stats-info-box">
                            <span class="stats-number">2/8 reviewed</span>
                            <span class="stats-label">Pages</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                     <div class="stats-card widget-skin">
                        <div class="stats-icon-box stats-product"></div>
                        <div class="stats-info-box">
                            <span class="stats-number">2/8 reviewed</span>
                            <span class="stats-label">Products</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="spliter"></div>

            <!-- Posts Table -->
            <div class="theme-banner widget-skin"></div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4 d-flex justify-content-end">
            <div class="theme-chart widget-skin"></div>
        </div>
    </div>
</div>