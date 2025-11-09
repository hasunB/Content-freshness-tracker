<?php

if (!defined('ABSPATH')) exit;

if (! current_user_can('edit_posts')) {
    echo '<p>' . esc_html__('No permission to view.', 'fresh-reminder') . '</p>';
    return;
}

$defaults = FR_Cron::get_default();
$settings = get_option(FR_OPTION_NAME, $defaults);

if ( isset( $_POST['post_types'] ) && check_admin_referer( 'fresh_reminder_action', 'fresh_reminder_nonce' ) ) {
    $raw_post_types = wp_unslash( $_POST['post_types'] ); // Unslash before sanitizing
    $post_types = array_map( 'sanitize_text_field', array_keys( $raw_post_types ) );
} else {
    $post_types = array( 'post' );
}

$args = array(
    'post_type'      => $post_types,
    'post_status'    => 'publish',
    'fields'         => 'ids',
    'posts_per_page' => -1,
);

$cache = get_option(FR_CACHE_OPTION);
$post_ids = isset($cache['post_ids']) ? array_unique($cache['post_ids']) : array();

$posts_data = array();
foreach ($post_ids as $post_id) {
    $post = get_post($post_id);
    if ($post && get_post_meta($post->ID, '_fr_pined', true)) {
        $posts_data[] = (object) array(
            'ID'                => $post->ID,
            'post_title'        => $post->post_title,
            'post_author_id'    => $post->post_author,
            'post_author_name'  => get_the_author_meta('display_name', $post->post_author),
            'post_type'         => $post->post_type,
            'post_date'         => $post->post_date,
            'post_modified'     => $post->post_modified,
            'reviewed'          => get_post_meta($post->ID, '_fr_reviewed', true) ? true : false,
            'pined'             => get_post_meta($post->ID, '_fr_pined', true) ? true : false,
            'edit_link'         => get_edit_post_link($post->ID),
            'featured_image'   => get_the_post_thumbnail_url($post->ID, 'thumbnail') ? get_the_post_thumbnail_url($post->ID, 'thumbnail') : FR_PLUGIN_URL . '/assets/images/logo/default-featured-' . $post->post_type . '.webp',
            'category_ids'      => !empty(get_object_taxonomies($post->post_type)) ? wp_get_post_terms($post->ID, get_object_taxonomies($post->post_type)[0], array('fields' => 'ids')) : array(),
        );
    }
}


?>
<div class="theme-container">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container-fluid d-flex align-items-center justify-content-center">
            <div class="col-9">
                <div class="d-flex align-items-center navbar-action-gap">
                    <div class="logo" style="border: none;">
                    </div>
                    <div class="theme-search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control" data-target="#searchable-content-box" placeholder="Search your content...">
                    </div>
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end navbar-action-gap">
                <div class="d-flex gap-3">
                    <button class="theme-action-btn goto-home-page" title="Home"><i class="fas fa-home"></i></button>
                    <button class="theme-action-btn goto-settings-page" title="Settings"><i class="fas fa-cog"></i></button>
                    <button class="theme-action-btn goto-help-page" title="help"><i class="fas fa-question"></i></button>
                </div>
                <div class="logo" style="background: none;">
                    <?php
                    $curent_user = wp_get_current_user();
                    if ($curent_user) {
                        //profile image
                        $profile_image = get_avatar_url($curent_user->ID, array('size' => 32));
                        if ($profile_image) {
                            echo '<img src="' . esc_url($profile_image) . '" alt="' . esc_attr__( 'Default User Avatar', 'fresh-reminder' ) . '" class="user-avatar">';
                        } else {
                            echo '<img src="' . esc_url( FR_PLUGIN_URL . '/assets/images/fr-default-user-profile.webp' ) . '" alt="' . esc_attr__( 'Default User Avatar', 'fresh-reminder' ) . '" class="user-avatar">';
                        }
                    } else {
                        echo '<img src="' . esc_url( FR_PLUGIN_URL . '/assets/images/fr-default-user-profile.webp' ) . '" alt="' . esc_attr__( 'Default User Avatar', 'fresh-reminder' ) . '" class="user-avatar">';
                    }
                    ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content-box">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Stale Posts -->
            <div class="theme-stale-content widget-skin" data-current-page="check-bucket-page">
                <!-- filters -->
                <div class="theme-filter-box">
                    <div class="col-4 d-flex align-items-center gap-2">
                        <span class="content-title">Check Bucket</span>
                        <img class="theme-warning-img" 
                        src="<?php echo esc_url(FR_PLUGIN_URL . '/assets/images/logo/fr-exclamation.png'); ?>" 
                        alt="fr-warning-icon" role="button" 
                        data-bs-toggle="popover" 
                        data-bs-trigger="hover" 
                        data-bs-placement="right" 
                        data-bs-content="Pin your most important posts here for quick access and to keep them prioritized.">
                    </div>
                    <div class="col-8 align-items-center d-flex justify-content-end gap-2">
                        <button class="filter-skin theme-filter-btn active" type="button" data-filter="all">All</button>
                        <button class="filter-skin theme-filter-btn" type="button" data-filter="reviewed">Reviewed</button>
                        <button class="filter-skin theme-filter-btn" type="button" data-filter="unreviewed">Unreviewed</button>
                    </div>
                </div>

                <!-- content -->
                <div class="theme-content-box">
                    <div class="post-item-box search-item-template">
                        <?php
                        foreach ($posts_data as $post) {
                                $reviewed_class = $post->reviewed ? 'fr-reviewed' : 'fr-unreviewed';
                        ?>
                                <div class="post-item <?php echo esc_attr($reviewed_class); ?>">
                                    <div style="width: 100%; height: 100%; display: flex; flex-direction: row;">
                                        <div style="width: 35%; height: inherit;">
                                            <div class="featured-image">
                                                <img src="<?php echo esc_html($post->featured_image) ?>" alt="fresh reminder default featured post icon">
                                            </div>
                                        </div>
                                        <div style="width: 65%; height: inherit;">
                                            <div style="height: 68%; width: 100%;">
                                                <h5 class="fw-semibold text-start text-break text-cut post-title"><?php echo esc_html($post->post_title); ?></h5>
                                                <p class="text-author">By <a href="#"><?php echo esc_html($post->post_author_name); ?></a></p>
                                            </div>
                                            <div class="h-30 w-100 d-flex align-items-end justify-content-end">
                                                <?php
                                                if ($post->pined) {
                                                ?>
                                                    <button type="button" class="pin-action-btn rotate-45 btn-pined" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="pined-post">
                                                        <i class="fas fa-thumbtack-slash"></i>
                                                    </button>
                                                <?php
                                                } 

                                                if ($post->reviewed) {
                                                ?>
                                                    <button type="button" class="review-action-btn btn-reviewed" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="pined-post">
                                                        <i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;<?php esc_html_e('Reviewed', 'fresh-reminder'); ?>
                                                    </button>
                                                <?php
                                                } else {
                                                ?>
                                                    <button type="button" class="review-action-btn btn-review" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="pined-post">
                                                        <i class="fa-solid fa-check"></i>&nbsp;&nbsp;<?php esc_html_e('Review', 'fresh-reminder'); ?>
                                                    </button>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        ?>
                    </div>
                    <div class="no-posts-box">
                        <p class="fw-semibold fs-6"><?php esc_html_e('No posts found for this filter.', 'fresh-reminder'); ?></p>
                    </div>
                </div>

                <!-- pagination -->
                <div class="theme-pagination-box">
                    <div class="demo-section">
                        <div class="pagination-glass">
                        </div>
                    </div>
                </div>
            </div>
            <div class="spliter left"></div>



            <!-- search result content -->
            <div id="searchable-content-box" class="search-result-content widget-skin">
                <h5 class="fw-semibold text-center ps-5 pe-5 mt-3">Search Result for :
                    <span class="search-query">hello world hello world</span>
                </h5>
                <!-- <div class="col-12 align-items-center d-flex justify-content-center gap-2 mt-2">
                    <button class="filter-skin theme-filter-btn active" type="button" data-filter="all">All</button>
                    <button class="filter-skin theme-filter-btn" type="button" data-filter="reviewed">Reviewed</button>
                    <button class="filter-skin theme-filter-btn" type="button" data-filter="unreviewed">Unreviewed</button>
                </div> -->
                <div class="theme-content-box">
                    <div class="post-item-box">
                        <?php
                        foreach ($posts_data as $post) {
                            $reviewed_class = $post->reviewed ? 'fr-reviewed' : 'fr-unreviewed';
                        ?>
                            <div class="post-item <?php echo esc_attr($reviewed_class); ?>">
                                <div style="width: 100%; height: 100%; display: flex; flex-direction: row;">
                                    <div style="width: 35%; height: inherit;">
                                        <div class="featured-image">
                                            <img src="<?php echo esc_html($post->featured_image) ?>" alt="fresh reminder default featured post icon">
                                        </div>
                                    </div>
                                    <div style="width: 65%; height: inherit;">
                                        <div style="height: 68%; width: 100%;">
                                            <h5 class="fw-semibold text-start text-break text-cut post-title"><?php echo esc_html($post->post_title); ?></h5>
                                            <p class="text-author">By <a href="#"><?php echo esc_html($post->post_author_name); ?></a></p>
                                        </div>
                                        <div class="h-30 w-100 d-flex align-items-end justify-content-end">
                                            <?php
                                            if ($post->pined) {
                                            ?>
                                                <button type="button" class="pin-action-btn rotate-45 btn-pined" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                                    <i class="fas fa-thumbtack-slash"></i>
                                                </button>
                                            <?php
                                            } 
                                            if ($post->reviewed) {
                                            ?>
                                                <button type="button" class="review-action-btn btn-reviewed" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                                    <i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;<?php esc_html_e('Reviewed', 'fresh-reminder'); ?>
                                                </button>
                                            <?php
                                            } else {
                                            ?>
                                                <button type="button" class="review-action-btn btn-review" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                                    <i class="fa-solid fa-check"></i>&nbsp;&nbsp;<?php esc_html_e('Review', 'fresh-reminder'); ?>
                                                </button>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="no-search-results-box">
                        <p class="fw-semibold fs-6"><?php esc_html_e('No posts found for this search.', 'fresh-reminder'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4 d-flex align-items-end flex-column">

            <!-- chart-widget -->
            <div class="theme-chart widget-skin">
                <div class="w-100 h-100">
                    <h5 class="chart-title">Freshness Tracking</h5>
                    <!-- content-box -->
                    <div class="w-100 h-100 chart-content-box" style="display: none;">
                        <p class="chart-description ps-5 pe-5">Your saving continue to grow by 5.0% every month</p>
                        <div class="pie-chart">
                            <canvas id="fr_piechart_canvas"></canvas>
                        </div>
                        <div class="w-100 chart-legend">
                            <div class="w-50 h-100">
                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                    <span class="legend-percentage reviewed">0%</span>
                                    <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                        <div class="legend-indicator indicator-reviewed"></div>
                                        <span class="legend-label">Reviewed</span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-50 h-100">
                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                    <span class="legend-percentage unreviewed">0%</span>
                                    <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                        <div class="legend-indicator indicator-unreviewed"></div>
                                        <span class="legend-label">Unreviewed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="chart-muted ps-5 pe-5 mt-3 mb-0">
                            Your saving continue to grow by 5.0% every month. Your saving continue to grow by 5.0% every month.
                        </p>
                    </div>
                    <!-- no-content-box -->
                    <div class="w-100 no-chart-content-box" style="display: none;">
                        <div></div>
                        <h5>No Data Found</h5>
                    </div>
                </div>
            </div>

            <!-- calendar-widget -->
            <!-- <div class="theme-chart widget-skin"></div> -->
        </div>
    </div>
    <!-- mobile responsive filter div -->
    <div class="mobile-responsive-filter-box">
        <div>
            <p>This page is best viewed on a desktop or tablet device for full functionality.</p>
        </div>
    </div>
</div>