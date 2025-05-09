jQuery(document).ready(function() {
    jQuery('#menu-header ul li').hover(function() {
        var hovered = jQuery(this).index();
        previous = hovered - 1;
        if (previous >= 0) {
            var sibling = jQuery('#menu-header li:first-child').prev().eq(previous);
            sibling.addClass('border-gone');
        }
    });
    jQuery('#menu-header ul li').mouseleave(function() {
        var hovered = jQuery(this).index();
        previous = hovered - 1;
        if (previous >= 0) {
            sibling = jQuery('#menu-header li').prev().eq(previous);
            sibling.removeClass('border-gone');
        }
    });
    if (jQuery('.filter_choice a').hasClass('active')) {
        if (jQuery('.filter-menu').data('order') == 'DESC') { 
            var direction = 'down';
        }
        else {
            var direction = 'up';
        }
        jQuery('.filter_choice a.active').append('&nbsp;<i class="fa fa-sort-' + direction + '"></i>');
    }
    if( jQuery('#header .nav-check').css('display') === 'none' || jQuery('#header .menu-toggle').css('display') === 'none' ) {
        jQuery("#menu-header ul li:has(ul)").addClass("noBottom");
        jQuery("#menu-header div.menu ul").addClass("defaultMenu");
        jQuery("#menu-header div.menu ul ul").removeClass("defaultMenu");
        jQuery("#menu-header ul li:has(ul)").addClass("defaultMenu");
        jQuery("#menu-header ul.defaultMenu ul.children").css({ display: 'none' });
        jQuery("#menu-header ul.defaultMenu li").hover(function() {
            jQuery(this).find('ul.children').stop(true, true).delay(40).animate({ "height": "show" }, 300 );
                
        }, function(){
            jQuery(this).find('ul.children').stop(true, true).delay(40).animate({ "height": "hide" }, 300 );
        });
        jQuery("#menu-header ul.menu ul.sub-menu").css({ display: 'none' });
        jQuery("#menu-header ul.menu > li").hover(function() {
            jQuery(this).children('ul.sub-menu').stop(true, true).delay(40).animate({ "height": "show" }, 300 );
                
        }, function(){
            jQuery(this).find('ul.sub-menu').stop(true, true).delay(40).animate({ "height": "hide" }, 300 );
        });
        jQuery("#menu-header ul.menu ul.sub-menu").css({ display: 'none' });
        jQuery("#menu-header ul.menu > li li").hover(function() {
            jQuery(this).children('ul.sub-menu').stop(true, true).delay(40).animate({ "height": "show" }, 300 );
                
        });
    }
    
    jQuery('.hasvideo').click(function() {
        var vidSource = jQuery('.hasvideo iframe').attr('src');
        if (vidSource.indexOf('?rel=0&autoplay=1') == -1) {
            jQuery('.hasvideo iframe').attr('src', vidSource + '?rel=0&autoplay=1').show();
        }
        else {
            jQuery('.hasvideo iframe').show();
        }
        jQuery('.hasvideo').css('height', 'auto').parent().css({'height':'auto', 'background-color':'transparent'});
    });
    /* Content_Tabs */
    jQuery("ul.content_tabs li").click(function(e){
        if (!jQuery(this).hasClass("active")) {
            var tabID = jQuery(this).attr('id');
            //var tabNum = jQuery(this).index();
            //var nthChild = tabNum+1;
            jQuery("ul.content_tabs li.active").removeClass("active");
            jQuery(this).addClass("active");
            jQuery(".content_tab_container .content_tab.active").removeClass("active").hide();
            //jQuery(".content_tab_container .content_tab:nth-child("+nthChild+")").addClass("active");
            jQuery('.content_tab.' + tabID).addClass('active').show();
        }
    });
    /**
     * Make all iFrames videos responsive
     */
    var $allVideos = jQuery('.ign-content-long iframe, .wp-block-embed__wrapper iframe, .entry-content iframe, .video iframe');
    $allVideos.each(function() {
        resizeVideo(this)  
    });
    // When the window is resized
    jQuery(window).resize(function() {
        // Resize all videos according to their own aspect ratio
        $allVideos.each(function() {
            resizeVideo(this)
        }); 
    
    // Kick off one resize to fix all videos on page load
    }).resize();
});
function resizeVideo(video) {
    var newWidth = jQuery(video).parent().width();
    jQuery(video).data('aspectRatio', video.height / video.width);
    jQuery(video).width(newWidth).height(newWidth * jQuery(video).data('aspectRatio'));
}