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
            'pined'             => get_post_meta( $post->ID, '_fr_pined', true ) ? true : false,
            'edit_link'         => get_edit_post_link( $post->ID ),
            'featured_image'   => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ) ? get_the_post_thumbnail_url( $post->ID, 'thumbnail' ) : FR_PLUGIN_URL . '/assets/images/logo/default-featured-'.$post->post_type.'.webp',
        );
    }
}

$total_stale_posts = count( $posts_data );
$reviewed_posts_count = count( array_filter( $posts_data, function( $post ) { return $post->reviewed; } ) );
$unreviewed_posts_count = $total_stale_posts - $reviewed_posts_count;


$categorized_posts = array();
foreach ( $posts_data as $post ) {
    $post_type = $post->post_type;
    if ( ! isset( $categorized_posts[ $post_type ] ) ) {
        $categorized_posts[ $post_type ] = array(
            'reviewed' => 0,
            'unreviewed' => 0,
            'total' => 0,
        );
    }
    if ( $post->reviewed ) {
        $categorized_posts[ $post_type ]['reviewed']++;
    } else {
        $categorized_posts[ $post_type ]['unreviewed']++;
    }
    $categorized_posts[ $post_type ]['total']++;
}


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
            <!-- <div class="theme-banner widget-skin"></div>
            <div class="spliter"></div> -->

            <!-- Stats Cards -->
            <div class="row">
                <?php
                    foreach ( $categorized_posts as $post_type => $counts ) {
                        ?>
                            <div class="col-md-4">
                                <div class="stats-card widget-skin">
                                    <div class="stats-icon-box stats-<?php echo esc_attr( $post_type ); ?>">
                                        <img src="<?php echo FR_PLUGIN_URL . '/assets/images/logo/fr-' . esc_attr( $post_type ) . '-logo.webp'; ?>" alt="fresh reminder <?php echo esc_attr( $post_type ); ?> icon">
                                    </div>
                                    <div class="stats-info-box">
                                        <span class="stats-number"><?php echo esc_html( $counts['reviewed'] ); ?>/<?php echo esc_html( $counts['total'] ); ?> reviewed</span>
                                        <span class="stats-label"><?php echo esc_html( ucfirst( $post_type ) ); ?>s</span>
                                    </div>
                                </div>
                            </div>
                        <?php
                    }
                ?>
            </div>
            <div class="spliter"></div>

            <?php
                 foreach ( $categorized_posts as $post_type => $counts ) {
                    ?>
                        <div class="theme-stale-content widget-skin">
                            <!-- filters -->
                            <div class="theme-filter-box">
                                <div class="col-4 d-flex align-items-center gap-2">
                                    <span class="fs-5 fw-semibold">Stale <?php echo esc_html( ucfirst( $post_type ) ); ?>s</span>
                                    <div class="theme-question-box">
                                        <i class="fa-solid fa-exclamation fw-bold" style="font-size: 12px;"></i>
                                    </div>
                                </div>
                                <div class="col-8 align-items-center d-flex justify-content-end gap-2">
                                    <button class="filter-skin theme-filter-btn active" type="button" data-filter="all">All</button>
                                    <?php
                                    // Category filter dropdown - to be populated dynamically according to post type
                                    $taxonomy_name = '';
                                    if ( 'product' === $post_type ) {
                                        $taxonomy_name = 'product_cat';
                                    } else {
                                        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
                                        foreach ( $taxonomies as $taxonomy ) {
                                            if ( $taxonomy->hierarchical && $taxonomy->public ) {
                                                $taxonomy_name = $taxonomy->name;
                                                break; 
                                            }
                                        }
                                    }

                                    if ( ! empty( $taxonomy_name ) ) {
                                        $taxonomy_obj = get_taxonomy( $taxonomy_name );
                                        $categories = get_terms( array(
                                            'taxonomy'   => $taxonomy_name,
                                            'hide_empty' => false,
                                        ) );
                                        if ( ! empty( $categories ) ) {
                                            ?>
                                                <select class="filter-skin" style="background-color: var(--background-color);  
                                                border-radius: 20px; 
                                                color: var(--primary-color); 
                                                font-weight: 500; 
                                                font-size: 13px; 
                                                padding-right: 30px; 
                                                padding-left: 20px;"
                                                >
                                                    <option value="0">Select <?php echo esc_html( $taxonomy_obj->labels->singular_name ) ?></option>
                                                    <?php
                                                        foreach ( $categories as $category ) {
                                                            echo '<option value="' . esc_attr( $category->term_id ) . '">' . esc_html( $category->name ) . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            <?php
                                        } else {
                                            ?>
                                                <select class="filter-skin" style="background-color: var(--background-color);  
                                                border-radius: 20px; 
                                                color: var(--primary-color); 
                                                font-weight: 500; 
                                                font-size: 13px; 
                                                padding-right: 30px; 
                                                padding-left: 20px;"
                                                >
                                                    <option value="0">Select <?php echo esc_html( $taxonomy_obj->labels->singular_name ) ?></option>
                                                </select>
                                            <?php
                                        }
                                    }

                                    ?>
                                    <button class="filter-skin theme-filter-btn" type="button" data-filter="reviewed">Reviewed</button>
                                    <button class="filter-skin theme-filter-btn" type="button" data-filter="unreviewed">Unreviewed</button>
                                    <button class="filter-skin theme-minimize-btn" type="button">
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- content -->
                            <div class="theme-content-box">
                                <div class="post-item-box">
                                    <?php
                                    foreach ( $posts_data as $post ) {
                                        if ( $post->post_type !== $post_type ) {
                                            continue;
                                        } else {
                                            $reviewed_class = $post->reviewed ? 'fr-reviewed' : 'fr-unreviewed';
                                            ?>
                                            <div class="post-item <?php echo esc_attr( $reviewed_class ); ?>">
                                                <div style="width: 100%; height: 100%; display: flex; flex-direction: row;">
                                                    <div style="width: 35%; height: inherit;">
                                                        <div class="featured-image">
                                                            <img src="<?php echo esc_html( $post->featured_image ) ?>" alt="fresh reminder default featured post icon">
                                                        </div>
                                                    </div>
                                                    <div style="width: 65%; height: inherit;">
                                                        <div style="height: 68%; width: 100%;">
                                                            <h5 class="fw-semibold text-start text-break text-cut" ><?php echo esc_html( $post->post_title ); ?></h5>
                                                            <p class="text-author">By <a href="#"><?php echo esc_html( $post->post_author_name ); ?></a></p>
                                                        </div>
                                                        <div class="h-30 w-100 d-flex align-items-end justify-content-end">
                                                            <button type="button" class="pin-action-btn rotate-45">
                                                                <i class="fas fa-thumbtack"></i>
                                                            </button>
                                                            <?php
                                                            if ( $post->reviewed ) {
                                                                ?>
                                                                    <button type="button" class="review-action-btn btn-reviewed" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                                                        <i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;<?php esc_html_e( 'Reviewed', 'fresh-reminder' ); ?>
                                                                    </button>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                    <button type="button" class="review-action-btn btn-review" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                                                        <i class="fa-solid fa-check"></i>&nbsp;&nbsp;<?php esc_html_e( 'Review', 'fresh-reminder' ); ?>
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
                                    <p class="fw-semibold fs-6"><?php esc_html_e( 'No posts found for this filter.', 'fresh-reminder' ); ?></p>
                                </div>
                            </div>

                            <!-- pagination -->
                            <div class="theme-pagination-box">
                                <div class="demo-section">
                                    <div class="pagination-glass">
                                        <a class="page-link nav-btn" href="#"><i style="font-size: 13px;" class="fas fa-chevron-left"></i></a>
                                        <a class="page-link active" href="#">1</a>
                                        <a class="page-link" href="#">2</a>
                                        <a class="page-link" href="#">3</a>
                                        <a class="page-link" href="#">4</a>
                                        <a class="page-link" href="#">5</a>
                                        <a class="page-link nav-btn" href="#"><i style="font-size: 13px;" class="fas fa-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="spliter"></div>
                    <?php
                }
            ?>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4 d-flex align-items-end flex-column">

            <!-- chart-widget -->
            <div class="theme-chart widget-skin">
                <div class="w-100 h-100">
                    <h5 class="chart-title">Freshness Tracking</h5>
                    <p class="chart-description ps-5 pe-5" >Your saving continue to grow by 5.0% every month</p>
                    <?php
                        // Prepare data for the chart
                        $chart_data = array(
                            array('Status', 'Count'),
                            array('Reviewed', $reviewed_posts_count),
                            array('Unreviewed', $unreviewed_posts_count),
                        );

                        //count percentages
                        $total = $reviewed_posts_count + $unreviewed_posts_count;
                        $reviewed_percentage = $total > 0 ? round(($reviewed_posts_count / $total) * 100) : 0;
                        $unreviewed_percentage = $total > 0 ? round(($unreviewed_posts_count / $total) * 100) : 0;

                        // Enqueue Google Charts library
                        wp_enqueue_script('google-charts', 'https://www.gstatic.com/charts/loader.js', array(), null, true);
                        wp_add_inline_script('google-charts', '
                            google.charts.load("current", {packages:["corechart"]});
                            google.charts.setOnLoadCallback(drawChart);

                            function drawChart() {
                                var data = google.visualization.arrayToDataTable(' . json_encode($chart_data) . ');

                                var options = {
                                    legend: "none",
                                    pieHole: 0.5,
                                    backgroundColor	: "transparent",
                                    colors: ["#ECE9FF", "#8238EF"] 
                                };

                                var chart = new google.visualization.PieChart(document.getElementById("fr_piechart"));
                                chart.draw(data, options);
                            }
                        ');
                    ?>
                    <div id="fr_piechart" class="pie-chart"></div>
                    <div class="w-100 chart-legend">
                        <div class="w-50 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage" ><?php echo esc_html( $reviewed_percentage ); ?>%</span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <div class="legend-indicator indicator-reviewed"></div>
                                    <span class="legend-label" >Reviewed</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-50 h-100">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <span class="legend-percentage" ><?php echo esc_html( $unreviewed_percentage ); ?>%</span>
                                <div class="d-flex flex-row align-items-center justify-content-center gap-2">
                                    <div class="legend-indicator indicator-unreviewed"></div>
                                    <span class="legend-label" >Unreviewed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="chart-muted ps-5 pe-5 mt-3">
                        Your saving continue to grow by 5.0% every month. Your saving continue to grow by 5.0% every month.
                    </p>
                </div>

            </div>
            <div class="spliter"></div>

            <!-- calendar-widget -->
            <div class="theme-chart widget-skin"></div>
        </div>
    </div>
</div>

