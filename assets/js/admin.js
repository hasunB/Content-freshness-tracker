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
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });
});