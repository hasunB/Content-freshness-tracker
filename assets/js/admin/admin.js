jQuery(function($){
    const itemsPerPage = 3;

    function showPage(container, page) {
        var posts = container.find('.post-item.fr-visible');
        var startIndex = (page - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        // Hide all posts in the container first, the shw the paginated slice
        container.find('.post-item').hide(); 
        posts.slice(startIndex, endIndex).show();

        container.find('.pagination-glass .page-link').removeClass('active');
        container.find('.pagination-glass .page-link[data-page="' + page + '"]').addClass('active');

    }

    function setupPagination(container) {
        var posts = container.find('.post-item.fr-visible');
        var totalPosts = posts.length;
        var totalPages = Math.ceil(totalPosts / itemsPerPage);
        var paginationBox = container.find('.theme-pagination-box');
        var paginationGlass = paginationBox.find('.pagination-glass');
        var noPostsMessage = container.find('.no-posts-box');

        paginationGlass.empty();

        if (totalPosts === 0) {
            noPostsMessage.css('display', 'flex');
            paginationBox.hide();
            container.find('.post-item').hide(); //hide all posts
            return;
        } else {
            noPostsMessage.hide();
        }

        if (totalPages <= 1) {
            paginationBox.hide();
            container.find('.post-item').hide(); //hide all posts
            posts.show(); //show all visible posts
            return;
        }

        paginationBox.show();

        paginationGlass.append('<a class="page-link nav-btn prev" href="#"><i style="font-size: 13px;" class="fas fa-chevron-left"></i></a>');

        for (let i = 1; i <= totalPages; i++) {
            paginationGlass.append('<a class="page-link" href="#" data-page="' + i + '">' + i + '</a>');
        }

        paginationGlass.append('<a class="page-link nav-btn next" href="#"><i style="font-size: 13px;" class="fas fa-chevron-right"></i></a>');

        showPage(container, 1);

        //used .off() .on() to prevent multiple bindings from event handlers
        paginationGlass.off('click', '.page-link').on('click', '.page-link', function(e){
            e.preventDefault();
            var btn = $(this);
            var currentPage = parseInt(container.find('.page-link.active').data('page'));

            if (btn.hasClass('prev')) {
                if (currentPage > 1) {
                    showPage(container, currentPage - 1);
                }
            } else if (btn.hasClass('next')) {
                if (currentPage < totalPages) {
                    showPage(container, currentPage + 1);
                }
            } else {
                var selectedPage = parseInt(btn.data('page'));
                showPage(container, selectedPage);
            }

        });

    }

    // Review/Unreview button handlers
    $(document).on('click', '.btn-review', function(e){
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id){
            alert('No ID Found');
            return;
        }

        if (!postType){
            alert('No Post Type Found');
            return;
        }

        $.post(fr_ajax.ajax_url, {
            action: 'fr_mark_reviewed',
            nonce: fr_ajax.nonce,
            post_id: id
        }, function(resp){
            if (resp && resp.success) {
                var newBtn = $('<button class="review-action-btn btn-reviewed" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                                '<i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;Reviewed' +
                               '</button>');
                var postItem = btn.closest('.post-item');
                postItem.removeClass('fr-unreviewed').addClass('fr-reviewed');
                btn.replaceWith(newBtn);
                setupPagination(postItem.closest('.theme-stale-content'));
                refreshStatsCard(postType, true);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.btn-reviewed', function(e){
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id){
            alert('No ID Found');
            return;
        }

        if (!postType){
            alert('No Post Type Found');
            return;
        }

        $.post(fr_ajax.ajax_url, {
            action: 'fr_unmark_reviewed',
            nonce: fr_ajax.nonce,
            post_id: id
        }, function(resp){
            if (resp && resp.success) {
                var newBtn = $('<button class="review-action-btn btn-review" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                                '<i class="fa-solid fa-check"></i>&nbsp;&nbsp;Review' +
                               '</button>');
                var postItem = btn.closest('.post-item');
                postItem.removeClass('fr-reviewed').addClass('fr-unreviewed');
                btn.replaceWith(newBtn);
                setupPagination(postItem.closest('.theme-stale-content'));
                refreshStatsCard(postType, false);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    // Pin/Unpin button handlers
    $(document).on('click', '.btn-pin', function(e){
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id){
            alert('No ID Found');
            return;
        }

        if (!postType){
            alert('No Post Type Found');
            return;
        }

        $.post(fr_ajax.ajax_url, {
            action: 'fr_mark_pined',
            nonce: fr_ajax.nonce,
            post_id: id
        }, function(resp){
            if (resp && resp.success) {
                var newBtn = $('<button class="pin-action-btn rotate-45 btn-pined" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                                '<i class="fas fa-thumbtack"></i>' +
                               '</button>');
                var postItem = btn.closest('.post-item');
                postItem.addClass('fr-pined');
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.btn-pined', function(e){
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id){
            alert('No ID Found');
            return;
        }

        if (!postType){
            alert('No Post Type Found');
            return;
        }

        $.post(fr_ajax.ajax_url, {
            action: 'fr_unmark_pined',
            nonce: fr_ajax.nonce,
            post_id: id
        }, function(resp){
            if (resp && resp.success) {
                var newBtn = $('<button class="pin-action-btn rotate-45 btn-pin" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                                '<i class="fas fa-thumbtack"></i>' +
                               '</button>');
                var postItem = btn.closest('.post-item');
                postItem.removeClass('fr-pined');
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    // Filter button handlers
    $(document).on('click', '.theme-filter-btn', function() {
        var filter = $(this).data('filter');
        var container = $(this) .closest('.theme-stale-content');
        var contentBox = container.find('.theme-content-box');
        var posts = contentBox. find('.post-item');

        // Active button class
        container.find('.theme-filter-btn').removeClass('active');
        $(this).addClass('active');

        // Add/remove a class to mark which posts should be visible
        posts.removeClass('fr-visible');
        if (filter === 'all') {
            posts.addClass('fr-visible');
        } else {
            contentBox.find('.fr-'+ filter).addClass('fr-visible');
        }

        // Let pagination handle the show/hide
        setupPagination(container);

    });

    $(document).on('change', 'select.filter-skin', function(){
        var select = $(this);
        var catId = select.val();
        var container = select.closest('.theme-stale-content');
        var contentBox = container. find('.theme-content-box');
        var posts = contentBox. find('.post-item');

        // Reset status filters when category changes
        container.find('.theme-filter-btn').removeClass('active');
        container.find('.theme-filter-btn[data-filter="all"]').addClass('active');

        // Add/remove a class to mark which posts should be visible
        posts.removeClass('fr-visible');
        if ( catId === '0' ) {
            posts.addClass('fr-visible');
        } else {
            contentBox.find('.category-' + catId).addClass('fr-visible');
        }

        // Let pagination handle the show/hide
        setupPagination(container);

    });

    //page navigation buttons
    $(document).on('click', '.goto-check-bucket-page', function(){
        window.location.href = fr_admin_urls.check_bucket_page;
    });

    $(document).on('click', '.goto-settings-page', function(){
        window.location.href = fr_admin_urls.settings_page;
    });

    $(document).on('click', '.goto-help-page', function(){
        window.open(fr_admin_urls.help_page, '_blank');
    });

    // Initial setup
    $('.theme-stale-content').each(function(){
        var container = $(this);
        container.find('.post-item').addClass('fr-visible'); // Initially mark all posts as visible
        setupPagination(container);
    });

    //live dashboard updates
    function refreshStatsCard(postType, reviewed) {
        //update pie chart
        if (typeof fr_PieChart !== 'undefined' && fr_PieChart) {
            const chartDataSet = fr_PieChart.data.datasets[0];

            let currentReviewed = chartDataSet.data[0];
            let currentUnreviewed = chartDataSet.data[1];

            if (reviewed) {
                currentReviewed++;
                currentUnreviewed--;
            } else {
                currentReviewed--;
                currentUnreviewed++;
            }

            // Update the data table
            chartDataSet.data[0] = currentReviewed;
            chartDataSet.data[1] = currentUnreviewed;

            // Redraw the chart with updated data
            fr_PieChart.update();

            const total = currentReviewed + currentUnreviewed;
            const reviewedPct = Math.round((currentReviewed / total) * 100);
            const unreviewedPct = Math.round((currentUnreviewed / total) * 100);

            $('.legend-percentage.reviewed').text(reviewedPct + '%');
            $('.legend-percentage.unreviewed').text(unreviewedPct + '%');
        } 

        if(postType == 'pined-post'){
            return; // Currently, stats card update is only for 'post' post type
        }

        //update stats card

        var statsCard = $('.stats-' + postType).closest('.stats-card');
        var statsNumberElem = statsCard.find('.stats-number');

        var currentStatsText = statsNumberElem.text();
        var matches = currentStatsText.match(/(\d+)\/(\d+)/);

        if (matches) {
            var reviewedCount = parseInt(matches[1], 10);
            var totalCount = parseInt(matches[2], 10);

            if (reviewed) {
                reviewedCount++; // Increment reviewed count
            } else {
                reviewedCount--; // Decrement reviewed count
            }

            // Update the stats number display
            statsNumberElem.text(reviewedCount + '/' + totalCount + ' reviewed');
        }

        
    }

    //search box handler
    $(document).on('input', '.form-control[data-target]', function(){
        var input = $(this);
        var query = input.val().toLowerCase();


        var targetSelector = input.data('target');
        var container = $(targetSelector);

        if (container.length === 0) {
            alert('No target container found for search.');
            return;
        }

        var stalePostsContainer = $('.theme-stale-content');
        var statsCardBox = $('.stats-cards-box');
        var spliterBox = $('.spliter.left');

        var contentBox = container.find('.theme-content-box');
        var searchResultsBox = contentBox.find('.post-item-box');
        var posts = searchResultsBox.find('.post-item');
        var noPostsMessage = container.find('.no-search-results-box');
        var searchQueryDisplay = container.find('.search-query');

        // Update the search query display
        
        if (query.length >= 3) {
            // If query is less than 3 characters, show all posts
            searchQueryDisplay.text(input.val());
            stalePostsContainer.hide();
            statsCardBox.hide();
            spliterBox.hide();
            // container.show();
            animationIn(container);

            // Reset status filters when search query changes
            container.find('.theme-filter-btn').removeClass('active');
            container.find('.theme-filter-btn[data-filter="all"]').addClass('active');

            // Show/hide posts based on search query
            posts.each(function(){
                var postItem = $(this);
                var title = postItem.find('.post-title').text().toLowerCase();
                if (title.includes(query)) {
                    postItem.addClass('fr-visible');
                } else {
                    postItem.removeClass('fr-visible');
                }
            });

            var visiblePosts = posts.filter('.fr-visible');
            if (visiblePosts.length === 0) {
                noPostsMessage.css('display', 'flex');
                searchResultsBox.css('display', 'none');
            } else {
                noPostsMessage.hide();
                searchResultsBox.css('display', 'flex');
            }
            
            // Let pagination handle the show/hide
            setupPagination(container);
        } else {
            // container.hide();
            animationOut(container);
            stalePostsContainer.show();
            statsCardBox.show();
            spliterBox.show();
            
            // Reset search - show all posts
            posts.addClass('fr-visible');
            setupPagination(container);
        }



    });

    function animationIn(container) {
        container.addClass('fade-up-hidden');
        container.css('display', 'flex');

        setTimeout(function(){
            container.addClass('is-visible');
        }, 60);
    }

    function animationOut(container) {
        container.removeClass('is-visible');
        setTimeout(function(){
            container.css('display', 'none');
        }, 60);
    }

});