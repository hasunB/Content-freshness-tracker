jQuery(function($){
    $(document).on('click', '.btn-review', function(e){
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id');
        if (!id){
            alert('No ID Found');
            return;
        }

        // btn.prop('disabled', true);
        $.post(fr_ajax.ajax_url, {
            action: 'fr_mark_reviewed',
            nonce: fr_ajax.nonce,
            post_id: id
        }, function(resp){
            if (resp && resp.success) {
                var newBtn = $('<button class="review-action-btn btn-reviewed" data-post-id="' + id + '">' +
                                '<i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;Reviewed' +
                               '</button>');
                btn.closest('.post-item').removeClass('fr-unreviewed').addClass('fr-reviewed');
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.btn-reviewed', function(e){
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id');
        if (!id){
            alert('No ID Found');
            return;
        }

        // btn.prop('disabled', true);
        $.post(fr_ajax.ajax_url, {
            action: 'fr_unmark_reviewed',
            nonce: fr_ajax.nonce,
            post_id: id
        }, function(resp){
            if (resp && resp.success) {
                var newBtn = $('<button class="review-action-btn btn-review" data-post-id="' + id + '">' +
                                '<i class="fa-solid fa-check"></i>&nbsp;&nbsp;Review' +
                               '</button>');
                btn.closest('.post-item').removeClass('fr-reviewed').addClass('fr-unreviewed');
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.theme-filter-btn', function() {
        var filter = $(this).data('filter');
        var container = $(this).closest('.theme-stale-content');
        var contentBox = container.find('.theme-content-box');
        var noPostsMessage = contentBox.find('.no-posts-box');
        var pagination = container.find('.theme-pagination-box');

        // Active button class
        container.find('.theme-filter-btn').removeClass('active');
        $(this).addClass('active');

        var posts = contentBox.find('.post-item');
        var visiblePosts;

        if (filter === 'all') {
            posts.show();
            visiblePosts = posts;
        } else if (filter === 'reviewed') {
            posts.hide();
            visiblePosts = contentBox.find('.fr-reviewed').show();
        } else if (filter === 'unreviewed') {
            posts.hide();
            visiblePosts = contentBox.find('.fr-unreviewed').show();
        }

        if (visiblePosts.length === 0) {
            noPostsMessage.css('display', 'flex');
            pagination.css('display', 'none');
        } else {
            noPostsMessage.hide();
        }
    });
});