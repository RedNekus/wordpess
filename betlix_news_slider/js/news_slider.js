jQuery(window).load( function(){
    if(jQuery(document.body).hasClass('news_slider')) {
        let flag_news = true;
        let sidebar = jQuery('#sidebar-post');
        let section = jQuery('#post-meta').data('section');
        document.addEventListener('scroll', function() {
            let headers = jQuery('[data-time]');
            let lst = 0;
            headers.each(function(i, el) {
                let top = el.getBoundingClientRect().top;
                if(top >= (screen.height/2))
                    return false;
                lst = i;
            });
            let link = headers.eq(lst).data('link');
            if( link!= document.location.href)
                history.pushState(null, null, link);
            let last = jQuery('[data-time]').last();
            let offsetTop = last.offset().top;
            if(flag_news && offsetTop - jQuery(window).scrollTop() <= 0) {
                flag_news = false;
                let time = last.data('time');
                if(time) {
                    sidebar.before('<div class="transition-loader-inner"><label></label><label></label><label></label><label></label><label></label><label></label></div>');
                    jQuery.ajax({
                        type: 'POST',
                        url:'/wp-admin/admin-ajax.php',
                        data: {action: 'news-slider-ajax-submit', category_id: section, date: time},
                        success: function(response) {
                            if(response) {
                                sidebar.before(response);
                                click_rating_button.call(jQuery('.b_n_r_blocks').last());
                                flag_news = true;
                            }
                            jQuery('.main-content .transition-loader-inner').eq(0).remove();
                        }
                    });
                }
            } 
        }, {passive: true});
    }
})