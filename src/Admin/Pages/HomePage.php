<?php

if (!defined('ABSPATH')) exit;

if (! current_user_can('edit_posts')) {
    echo '<p>' . esc_html__('No permission to view.', 'fresh-reminder') . '</p>';
    return;
}

$fresre_defaults = FRESRE_Cron::get_default();
$fresre_settings = get_option(FRESRE_OPTION_NAME, $fresre_defaults);

if ( isset( $_POST['post_types'] ) && check_admin_referer( 'fresh_reminder_action', 'fresh_reminder_nonce' ) ) {
    $fresre_raw_post_types = wp_unslash( $_POST['post_types'] ); // Unslash before sanitizing
    $fresre_post_types = array_map( 'sanitize_text_field', array_keys( $fresre_raw_post_types ) );
} else {
    $fresre_post_types = array( 'post' );
}

$fresre_args = array(
    'post_type'      => $fresre_post_types,
    'post_status'    => 'publish',
    'fields'         => 'ids',
    'posts_per_page' => -1,
); 

$fresre_cache = get_option(FRESRE_CACHE_OPTION);
$fresre_post_ids = isset($fresre_cache['post_ids']) ? array_unique($fresre_cache['post_ids']) : array();

$fresre_posts_data = array();
foreach ($fresre_post_ids as $post_id) {
    $post = get_post($post_id);
    if ($post) {
        $fresre_posts_data[] = (object) array(
            'ID'                => $post->ID,
            'post_title'        => $post->post_title,
            'post_author_id'    => $post->post_author,
            'post_author_name'  => get_the_author_meta('display_name', $post->post_author),
            'post_type'         => $post->post_type,
            'post_date'         => $post->post_date,
            'post_modified'     => $post->post_modified,
            'reviewed'          => get_post_meta($post->ID, '_fresre_reviewed', true) ? true : false,
            'pined'             => get_post_meta($post->ID, '_fresre_pined', true) ? true : false,
            'edit_link'         => get_edit_post_link($post->ID),
            'featured_image'   => get_the_post_thumbnail_url($post->ID, 'thumbnail') ? get_the_post_thumbnail_url($post->ID, 'thumbnail') : FRESRE_PLUGIN_URL . '/assets/images/logo/default-featured-' . $post->post_type . '.webp',
            'category_ids'      => !empty(get_object_taxonomies($post->post_type)) ? wp_get_post_terms($post->ID, get_object_taxonomies($post->post_type)[0], array('fields' => 'ids')) : array(),
        );
    }
}

$fresre_total_stale_posts = count($fresre_posts_data);
$fresre_reviewed_posts_count = count(array_filter($fresre_posts_data, function ($post) {
    return $post->reviewed;
}));
$fresre_unreviewed_posts_count = $fresre_total_stale_posts - $fresre_reviewed_posts_count;


$fresre_categorized_posts = array();
foreach ($fresre_posts_data as $post) {
    $post_type = $post->post_type;
    if (! isset($fresre_categorized_posts[$post_type])) {
        $fresre_categorized_posts[$post_type] = array(
            'reviewed' => 0,
            'unreviewed' => 0,
            'total' => 0,
        );
    }
    if ($post->reviewed) {
        $fresre_categorized_posts[$post_type]['reviewed']++;
    } else {
        $fresre_categorized_posts[$post_type]['unreviewed']++;
    }
    $fresre_categorized_posts[$post_type]['total']++;
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
                <div class="d-flex theme-action-box">
                    <button class="theme-action-btn goto-check-bucket-page" title="Check Bucket"><i class="fas fa-bucket"></i></button>
                    <button class="theme-action-btn goto-settings-page" title="Settings"><i class="fas fa-cog"></i></button>
                    <button class="theme-action-btn goto-help-page" title="Help"><i class="fas fa-question"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content-box">
        <!-- Left Column -->
        <div class="theme-left-column">
            <?php

            if ($fresre_total_stale_posts == 0) {
            ?>
                <div class="no-post-found-msg widget-skin">
                    <div>
                        <div></div>
                        <h3>No Posts Found</h3>
                    </div>
                </div>
            <?php
            } else {
            ?>
                <!-- Hero Section -->
                <div class="theme-banner widget-skin">
                    <div class="theme-banner-content-box">
                        <div class="col-11 banner-text-box">
                            <h5>Hello
                                <?php
                                $fresre_curent_user = wp_get_current_user();

                                if ($fresre_curent_user instanceof WP_User) {

                                    $fresre_first_name = $fresre_curent_user->first_name;
                                    $fresre_last_name  = $fresre_curent_user->last_name;
                                    $fresre_display_name = $fresre_curent_user->display_name;

                                    if (! empty($fresre_first_name) && ! empty($fresre_last_name)) {
                                        echo esc_html($fresre_first_name . ' ' . $fresre_last_name);
                                    } else {
                                        echo esc_html($fresre_display_name);
                                    }
                                }
                                ?>
                            </h5>
                            <h4>Welcome to</h4>
                            <h3>Fresh Reminder</h3>
                            <button type="button" class="goto-settings-page">Start Configure</button>
                        </div>
                        <div class="col-1 d-flex justify-content-end align-items-start">
                            <button type="button" class="banner-close-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="spliter banner"></div>

                <!-- Stats Cards -->
                <div class="row stats-cards-box">
                    <?php
                    foreach ($fresre_categorized_posts as $post_type => $fresre_counts) {
                    ?>
                        <div class="col-md-4">
                            <div class="stats-card widget-skin">
                                <div class="stats-icon-box stats-<?php echo esc_attr($post_type); ?>">
                                    <img src="<?php echo esc_url( FRESRE_PLUGIN_URL . '/assets/images/logo/fresre-' . esc_attr($post_type) . '-logo.webp'); ?>" alt="fresh reminder <?php echo esc_attr($post_type); ?> icon">
                                </div>
                                <div class="stats-info-box">
                                    <span class="stats-number"><?php echo esc_html($fresre_counts['reviewed']); ?>/<?php echo esc_html($fresre_counts['total']); ?> reviewed</span>
                                    <span class="stats-label"><?php echo esc_html(ucfirst($post_type)); ?>s</span>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="spliter left"></div>
                <!-- Stale Posts -->
                <?php
                foreach ($fresre_categorized_posts as $post_type => $fresre_counts) {
                ?>
                    <div class="theme-stale-content widget-skin" data-post-type="<?php echo esc_attr($post_type); ?>">
                        <!-- filters -->
                        <div class="theme-filter-box">
                            <div>
                                <span class="content-title">Stale <?php echo esc_html(ucfirst($post_type)); ?>s</span>
                            </div>
                            <div class="col-8 align-items-center d-flex justify-content-end gap-2">
                                <?php wp_nonce_field('fresre_filter_posts_nonce', 'fresre_filter_posts_nonce'); ?>
                                <button class="filter-skin theme-filter-btn active" type="button" data-filter="all">All</button>
                                <?php
                                // Category filter dropdown - to be populated dynamically according to post type
                                $fresre_taxonomy_name = '';
                                if ('product' === $post_type) {
                                    $fresre_taxonomy_name = 'product_cat';
                                } else {
                                    $fresre_taxonomies = get_object_taxonomies($post_type, 'objects');
                                    foreach ($fresre_taxonomies as $taxonomy) {
                                        if ($taxonomy->hierarchical && $taxonomy->public) {
                                            $fresre_taxonomy_name = $taxonomy->name;
                                            break;
                                        }
                                    }
                                }

                                if (! empty($fresre_taxonomy_name)) {
                                    $fresre_taxonomy_obj = get_taxonomy($fresre_taxonomy_name);
                                    $fresre_categories = get_terms(array(
                                        'taxonomy'   => $fresre_taxonomy_name,
                                        'hide_empty' => false,
                                    ));
                                    if (! empty($fresre_categories)) {
                                ?>
                                        <select class="theme-filter-select filter-skin" data-taxonomy="<?php echo esc_attr($fresre_taxonomy_name); ?>">
                                            <option value="0">Select <?php echo esc_html($fresre_taxonomy_obj->labels->singular_name) ?></option>
                                            <?php
                                            foreach ($fresre_categories as $fresre_category) {
                                                echo '<option value="' . esc_attr($fresre_category->term_id) . '">' . esc_html($fresre_category->name) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select class="theme-filter-select filter-skin" disabled>
                                            <option value="0">Select <?php echo esc_html($fresre_taxonomy_obj->labels->singular_name) ?></option>
                                        </select>
                                <?php
                                    }
                                }

                                ?>
                                <button class="filter-skin theme-filter-btn" type="button" data-filter="reviewed">Reviewed</button>
                                <button class="filter-skin theme-filter-btn" type="button" data-filter="unreviewed">Unreviewed</button>
                                <button class="filter-skin theme-minimize-btn" type="button" data-post-type="<?php echo esc_attr($post_type); ?>">
                                    <i class="fa-solid fa-caret-up"></i>
                                </button>
                            </div>
                        </div>

                        <!-- content -->
                        <div class="theme-content-box">
                            <div class="post-item-box post-item-template" id="post-item-box-<?php echo esc_attr($post_type); ?>">
                                <?php
                                foreach ($fresre_posts_data as $post) {
                                    if ($post->post_type !== $post_type) {
                                        continue;
                                    } else {

                                        $fresre_reviewed_class = $post->reviewed ? 'fresre-reviewed' : 'fresre-unreviewed';
                                        $fresre_category_classes = ' ';

                                        // Normal WP categories
                                        if (! empty($post->category_ids)) {
                                            foreach ($post->category_ids as $fresre_category_id) {
                                                $fresre_category_classes .= ' category-' . $fresre_category_id;
                                            }
                                        }

                                        // WooCommerce product categories
                                        if ($post->post_type === 'product') {

                                            $fresre_product_terms = wp_get_post_terms( $post->ID, 'product_cat', ['fields' => 'ids'] );

                                            if ( ! empty( $fresre_product_terms ) ) {
                                                foreach ( $fresre_product_terms as $cat_id ) {
                                                    $fresre_category_classes .= ' category-' . $cat_id;
                                                }
                                            }
                                        }
                                ?>
                                        <div class="post-item <?php echo esc_attr($fresre_reviewed_class); ?><?php echo esc_attr($fresre_category_classes); ?>">
                                            <div style="width: 100%; height: 100%; display: flex; flex-direction: row;">
                                                <div style="width: 35%; height: inherit;">
                                                    <div class="featured-image">
                                                        <img src="<?php echo esc_html($post->featured_image) ?>" alt="fresh reminder default featured post icon">
                                                    </div>
                                                </div>
                                                <div style="width: 65%; height: inherit;">
                                                    <div class="post-title-box">
                                                        <h5 class="fw-semibold text-start text-break text-cut post-title" data-edit-url="<?php echo esc_attr($post->edit_link); ?>"><?php echo esc_html($post->post_title); ?></h5>
                                                        <p class="text-author">By <a href="#"><?php echo esc_html($post->post_author_name); ?></a></p>
                                                    </div>
                                                    <div class="h-30 w-100 d-flex align-items-end justify-content-end">
                                                        <?php
                                                        if ($post->pined) {
                                                        ?>
                                                            <button type="button" class="pin-action-btn rotate-45 btn-pined" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="<?php echo esc_attr($post_type); ?>">
                                                                <i class="fas fa-thumbtack-slash"></i>
                                                            </button>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <button type="button" class="pin-action-btn rotate-45 btn-pin" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="<?php echo esc_attr($post_type); ?>">
                                                                <i class="fa-solid fa-thumbtack"></i>
                                                            </button>
                                                        <?php
                                                        }

                                                        if ($post->reviewed) {
                                                        ?>
                                                            <button type="button" class="review-action-btn btn-reviewed" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="<?php echo esc_attr($post_type); ?>">
                                                                <i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;<?php esc_html_e('Reviewed', 'fresh-reminder'); ?>
                                                            </button>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <button type="button" class="review-action-btn btn-review" data-post-id="<?php echo esc_attr($post->ID); ?>" data-post-type="<?php echo esc_attr($post_type); ?>">
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
                <?php
                }
                ?>
                <!-- search result content -->
                <div id="searchable-content-box" class="search-result-content widget-skin">
                    <h5 class="fw-semibold text-center ps-5 pe-5 mt-3">Search Result for :
                        <span class="search-query"></span>
                    </h5>
                    <div class="theme-content-box">
                        <div class="post-item-box search-item-template">
                            <?php
                            foreach ($fresre_posts_data as $post) {
                                $fresre_reviewed_class = $post->reviewed ? 'fresre-reviewed' : 'fresre-unreviewed';
                            ?>
                                <div class="post-item <?php echo esc_attr($fresre_reviewed_class); ?>">
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
                                                } else {
                                                ?>
                                                    <button type="button" class="pin-action-btn rotate-45 btn-pin" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                                        <i class="fas fa-thumbtack"></i>
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

            <?php
            }
            ?>
        </div>

        <!-- Right Column -->
        <div class="theme-right-column">
            <!-- chart-widget -->
            <div class="theme-chart widget-skin">
                <div class="w-100 h-100">
                    <h5 class="chart-title">Freshness Tracking</h5>
                    <!-- content-box -->
                    <div class="w-100 h-100 chart-content-box" style="display: none;">
                        <p class="chart-description ps-5 pe-5">A visual breakdown of content status.</p>
                        <div class="pie-chart">
                            <canvas id="fresre_piechart_canvas"></canvas>
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
                            This chart displays the percentage of reviewed versus unreviewed content, providing a quick overview of your content's freshness.
                        </p>
                    </div>
                    <!-- no-content-box -->
                    <div class="w-100 no-chart-content-box" style="display: none;">
                        <div></div>
                        <h5>No Data Found</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- mobile responsive filter div -->
    <div class="mobile-responsive-filter-box">
        <div>
            <p>This page is best viewed on a desktop or tablet device for full functionality.</p>
        </div>
    </div>
</div>