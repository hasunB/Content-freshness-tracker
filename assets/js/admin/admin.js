jQuery(function ($) {

    $(document).ready(function () {
        $('.search-result-content').hide();

        if (getCookie("fresre_banner_closed") === "false" || getCookie("fresre_banner_closed") === null) {
            $('.theme-banner').css('display', 'flex');
            $('.spliter.banner').css('display', 'block');
        } else {
            $('.theme-banner').hide();
            $('.spliter.banner').hide()
        }

        const minimizedList = getMinimizedList();

        minimizedList.forEach(function (postType) {
            const box = $('.theme-stale-content[data-post-type="' + postType + '"]');
            const btn = $('.theme-minimize-btn[data-post-type="' + postType + '"]');
            if (box.length) {
                box.addClass('minimized').css({ height: 75, overflow: 'hidden' });
                btn.html('<i class="fas fa-caret-down"></i>');
                filterBoxEnabled(box, false);
            }
        });

        $('[data-bs-toggle="popover"]').each(function () {
            new bootstrap.Popover($(this)[0]);
        });
    });

    var itemsPerPage;

    function showPage(container, page) {
        var posts = container.find('.post-item.fresre-visible');
        var startIndex = (page - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        // Hide all posts in the container first, the shw the paginated slice
        container.find('.post-item').hide();
        posts.slice(startIndex, endIndex).show();

        container.find('.pagination-glass .page-link').removeClass('active');
        container.find('.pagination-glass .page-link[data-page="' + page + '"]').addClass('active');

    }

    function setupPagination(container) {
        var posts = container.find('.post-item.fresre-visible');
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
        paginationGlass.off('click', '.page-link').on('click', '.page-link', function (e) {
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
    $(document).on('click', '.btn-review', function (e) {
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id) {
            alert('No ID Found');
            return;
        }

        if (!postType) {
            alert('No Post Type Found');
            return;
        }

        $.post(fresre_ajax.ajax_url, {
            action: 'fresre_mark_reviewed',
            nonce: fresre_ajax.nonce,
            post_id: id
        }, function (resp) {
            if (resp && resp.success) {
                var newBtn = $('<button class="review-action-btn btn-reviewed" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                    '<i class="fa-solid fa-check-double"></i>&nbsp;&nbsp;Reviewed' +
                    '</button>');
                var postItem = btn.closest('.post-item');
                postItem.removeClass('fresre-unreviewed').addClass('fresre-reviewed');
                btn.replaceWith(newBtn);
                setupPagination(postItem.closest('.theme-stale-content'));
                refreshStatsCard(postType, true);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.btn-reviewed', function (e) {
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id) {
            alert('No ID Found');
            return;
        }

        if (!postType) {
            alert('No Post Type Found');
            return;
        }

        $.post(fresre_ajax.ajax_url, {
            action: 'fresre_unmark_reviewed',
            nonce: fresre_ajax.nonce,
            post_id: id
        }, function (resp) {
            if (resp && resp.success) {
                var newBtn = $('<button class="review-action-btn btn-review" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                    '<i class="fa-solid fa-check"></i>&nbsp;&nbsp;Review' +
                    '</button>');
                var postItem = btn.closest('.post-item');
                postItem.removeClass('fresre-reviewed').addClass('fresre-unreviewed');
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
    $(document).on('click', '.btn-pin', function (e) {
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id) {
            alert('No ID Found');
            return;
        }

        if (!postType) {
            alert('No Post Type Found');
            return;
        }

        $.post(fresre_ajax.ajax_url, {
            action: 'fresre_mark_pined',
            nonce: fresre_ajax.nonce,
            post_id: id
        }, function (resp) {
            if (resp && resp.success) {
                var newBtn = $('<button class="pin-action-btn rotate-45 btn-pined" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                    '<i class="fas fa-thumbtack-slash"></i>' +
                    '</button>');
                var postItem = btn.closest('.post-item');
                postItem.addClass('fresre-pined');
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.btn-pined', function (e) {
        e.preventDefault();
        var btn = $(this), id = btn.data('post-id'), postType = btn.data('post-type');
        if (!id) {
            alert('No ID Found');
            return;
        }

        if (!postType) {
            alert('No Post Type Found');
            return;
        }

        $.post(fresre_ajax.ajax_url, {
            action: 'fresre_unmark_pined',
            nonce: fresre_ajax.nonce,
            post_id: id
        }, function (resp) {
            if (resp && resp.success) {
                var newBtn = $('<button class="pin-action-btn rotate-45 btn-pin" data-post-id="' + id + '" data-post-type="' + postType + '">' +
                    '<i class="fas fa-thumbtack"></i>' +
                    '</button>');
                var postItem = btn.closest('.post-item');
                postItem.removeClass('fresre-pined');
                if (postType === 'pined-post') {
                    postItem.remove(); //remove from the list if we are unpinning from pinned posts list
                }
                btn.replaceWith(newBtn);
            } else {
                btn.prop('disabled', false);
                alert(resp && resp.data ? resp.data : 'Response Error');
            }
        });
    });

    $(document).on('click', '.post-title', function (e) {
        e.preventDefault();
        var postItem = $(this);
        var editUrl = postItem.data('edit-url');
        if (editUrl) {
            window.open(editUrl, '_blank');
        }
    });

    // Filter button handlers
    $(document).on('click', '.theme-filter-btn', function () {
        var filter = $(this).data('filter');
        var container = $(this).closest('.theme-stale-content');
        var contentBox = container.find('.theme-content-box');
        var posts = contentBox.find('.post-item');

        // Active button class
        container.find('.theme-filter-btn').removeClass('active');
        $(this).addClass('active');

        container.find('select.filter-skin').val('0'); // Reset category filter when status filter changes

        // Add/remove a class to mark which posts should be visible
        posts.removeClass('fresre-visible');
        if (filter === 'all') {
            posts.addClass('fresre-visible');
        } else {
            contentBox.find('.fresre-' + filter).addClass('fresre-visible');
        }

        // Let pagination handle the show/hide
        setupPagination(container);

    });

    $(document).on('change', 'select.filter-skin', function () {
        var select = $(this);
        var catId = select.val();
        var container = select.closest('.theme-stale-content');
        var contentBox = container.find('.theme-content-box');
        var posts = contentBox.find('.post-item');

        // Reset status filters when category changes
        container.find('.theme-filter-btn').removeClass('active');
        container.find('.theme-filter-btn[data-filter="all"]').removeClass('active');

        // Add/remove a class to mark which posts should be visible
        posts.removeClass('fresre-visible');
        if (catId === '0') {
            posts.addClass('fresre-visible');
        } else {
            contentBox.find('.category-' + catId).addClass('fresre-visible');
        }

        // Let pagination handle the show/hide
        setupPagination(container);

    });

    //page navigation buttons
    $(document).on('click', '.goto-home-page', function () {
        window.location.href = fresre_admin_urls.home_page;
    });

    $(document).on('click', '.goto-check-bucket-page', function () {
        window.location.href = fresre_admin_urls.check_bucket_page;
    });

    $(document).on('click', '.goto-settings-page', function () {
        window.location.href = fresre_admin_urls.settings_page;
    });

    $(document).on('click', '.goto-help-page', function () {
        window.open(fresre_admin_urls.help_page, '_blank');
    });

    // Initial setup
    $('.theme-stale-content').each(function () {
        var container = $(this);
        var page = container.data('current-page') || 1;

        if (page == 'check-bucket-page') {
            itemsPerPage = 12;
        } else {
            itemsPerPage = 6;
        }

        container.find('.post-item').addClass('fresre-visible'); // Initially mark all posts as visible
        setupPagination(container);
    });

    //live dashboard updates
    function refreshStatsCard(postType, reviewed) {
        //update pie chart
        if (typeof fresre_PieChart !== 'undefined' && fresre_PieChart) {
            const chartDataSet = fresre_PieChart.data.datasets[0];

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
            fresre_PieChart.update();

            const total = currentReviewed + currentUnreviewed;
            const reviewedPct = Math.round((currentReviewed / total) * 100);
            const unreviewedPct = Math.round((currentUnreviewed / total) * 100);

            $('.legend-percentage.reviewed').text(reviewedPct + '%');
            $('.legend-percentage.unreviewed').text(unreviewedPct + '%');
        }

        if (postType == 'pined-post') {
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
    $(document).on('input', '.form-control[data-target]', function () {
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
            posts.each(function () {
                var postItem = $(this);
                var title = postItem.find('.post-title').text().toLowerCase();
                if (title.includes(query)) {
                    postItem.addClass('fresre-visible');
                } else {
                    postItem.removeClass('fresre-visible');
                }
            });

            var visiblePosts = posts.filter('.fresre-visible');
            if (visiblePosts.length === 0) {
                noPostsMessage.css('display', 'flex');
                searchResultsBox.css('display', 'none');
            } else {
                noPostsMessage.hide();
                searchResultsBox.css('display', 'grid');
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
            posts.addClass('fresre-visible');
            setupPagination(container);
        }

    });

    function animationIn(container) {
        container.addClass('fade-up-hidden');
        container.css('display', 'grid');

        setTimeout(function () {
            container.addClass('is-visible');
        }, 60);
    }

    function animationOut(container) {
        container.removeClass('is-visible');
        setTimeout(function () {
            container.css('display', 'none');
        }, 60);
    }

    //When user closes the banner
    $(document).on('click', '.banner-close-btn', function () {
        $('.theme-banner').hide();
        $('.spliter.banner').hide();

        // set a cookie to remember the banner is closed for 7 days
        var d = new Date();
        d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000)); // 7 days
        var expires = "expires=" + d.toUTCString();

        //Set cookie for this page path (or you can use path=/ to make it global)
        document.cookie = "fresre_banner_closed=true;" + expires + ";path=/";
    });

    //content minize/maximize
    $(document).on('click', '.theme-minimize-btn', function () {
        var btn = $(this);
        var postType = btn.data('post-type');
        var contentBox = $('.theme-stale-content[data-post-type="' + postType + '"]');

        let minimizedList = getMinimizedList();

        if (contentBox.hasClass('minimized')) {
            // EXPAND
            var fullHeight = contentBox.get(0).scrollHeight; // actual full height
            contentBox
                .removeClass('minimized')
                .animate({ height: fullHeight }, 300, function () {
                    // after animation, reset to auto for flexibility
                    contentBox.css('height', 'auto');
                });
            btn.html('<i class="fas fa-caret-up"></i>');
            filterBoxEnabled(contentBox, true);

            // Remove from cookie list
            minimizedList = minimizedList.filter(item => item !== postType);

        } else {
            // MINIMIZE
            var currentHeight = contentBox.outerHeight();
            contentBox
                .css('height', currentHeight) // fix current height to start animation
                .animate({ height: 75 }, 300, function () {
                    contentBox.addClass('minimized');
                });
            btn.html('<i class="fas fa-caret-down"></i>');
            filterBoxEnabled(contentBox, false);

            // Add to cookie list if not already there
            if (!minimizedList.includes(postType)) {
                minimizedList.push(postType);
            }
        }

        //Update cookie
        setCookie('fresre_minimized_boxes', JSON.stringify(minimizedList), 7);
    });

    function filterBoxEnabled(contentBox, status) {
        var filterButtons = contentBox.find('.theme-filter-btn');
        var categorySelect = contentBox.find('select.filter-skin');

        if (status) {
            filterButtons.disabled = false;
            filterButtons.removeClass('btn-disabled');
            categorySelect.disabled = false;
            categorySelect.removeClass('btn-disabled');
        } else {
            filterButtons.disabled = true;
            filterButtons.addClass('btn-disabled');
            categorySelect.disabled = true;
            categorySelect.addClass('btn-disabled');
        }
    }

    //Cookie Helper functions
    //To set cookies
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    //To read cookies
    function getCookie(name) {
        let cookieArr = document.cookie.split(";");
        for (let i = 0; i < cookieArr.length; i++) {
            let cookie = cookieArr[i].trim();
            if (cookie.indexOf(name + "=") === 0) {
                return cookie.substring(name.length + 1);
            }
        }
        return null;
    }

    function getMinimizedList() {
        try {
            const cookie = getCookie('fresre_minimized_boxes');
            return cookie ? JSON.parse(cookie) : [];
        } catch (e) {
            return [];
        }
    }

    function popoverInit() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        })
    }


});