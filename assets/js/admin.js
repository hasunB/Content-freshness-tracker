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

            var legendReviewed = $('.legend-percentage.reviewed');
            var legendUnreviewed = $('.legend-percentage.unreviewed');

            var totalPosts = currentReviewed + currentUnreviewed;
            var newReviewedPercentage = Math.round((currentReviewed.getValue(0, 1) / totalPosts) * 100);
            var newUnreviewedPercentage = Math.round((currentUnreviewed / totalPosts) * 100);

            legendReviewed.text(newReviewedPercentage + '%');
            legendUnreviewed.text(newUnreviewedPercentage + '%');
        }
    }
});