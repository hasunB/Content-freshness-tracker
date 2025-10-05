jQuery(function($){
    $(document).on('click', '.fr-mark-review', function(e){
        e.preventDefault();
        var btn = $(this);
        var post_id = btn.closest('tr').find('.post-title').attr('href').split('post=')[1].split('&')[0];
        var id = btn.data('post-id');
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
                var newBtn = $('<button class="theme-btn bg-product fr-mark-unreview" data-post-id="' + id + '">' +
                                '<i class="fas fa-undo me-1"></i> Unmark Reviewed' +
                               '</button>');
                btn.replaceWith(newBtn);
                var row = newBtn.closest('tr');
                var badge = row.find('.status-badge');
                badge.text('Reviewed').removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success');
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.fr-mark-unreview', function(e){
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
                var newBtn = $('<button class="theme-btn bg-post fr-mark-review" data-post-id="' + id + '">' +
                                '<i class="fas fa-check me-1"></i> Mark Reviewed' +
                               '</button>');
                btn.replaceWith(newBtn);
                var row = newBtn.closest('tr');
                var badge = row.find('.status-badge');
                badge.text('Stale').removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger');
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });
});