jQuery(document).ready(function($) {
    // Preloader
    $('.loader').fadeOut();
    $('.page-loader').delay(350).fadeOut('slow');

    $(window).on('load', function() {
        if ($(window).scrollTop() <= 100) {
            $('.top').hide();
        }
    });

    // On scroll event listener for header
    $(window).scroll(function() {
        $("header").toggleClass("scrolled", $(this).scrollTop() > 20);

        if ($("header").hasClass("scrolled")) {
            $('#containerwrapper').css('padding-top', '0');
        } else {
            $('#containerwrapper').css('padding-top', '');
        }
    });

    $("header").toggleClass("scrolled", $(this).scrollTop() > 20);

    // Elements to animate
    const elementsToAnimate = $('.entry-title, .foundation_contents, .short-intro, .ign-more-projects, #filters, .stories-grid, .footer_title, .footer_content, .bussiness_identity, .footer_copyright, .ign-project-summary, .project-lists .item, .load-more-wrapper');

    // Intersection Observer for fadeUp animation
    const fadeUpObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                $(entry.target).addClass('fadeInUp');
                fadeUpObserver.unobserve(entry.target); // Stop observing once animated
            }
        });
    });

    // Observe each element to animate
    elementsToAnimate.each(function() {
        fadeUpObserver.observe(this);
    });

    // Debounced scroll event listener for "top" button
    let debounceTimeout;
    $(window).on('scroll', function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(function() {
            if ($(window).scrollTop() > 100) {
                $('.top').fadeIn();
            } else {
                $('.top').fadeOut();
            }
        }, 100);
    });

    // Smooth scroll to top when "top" button is clicked
    $('.top').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });

    // Trigger the scroll event on load to initiate animations on elements already in view
    $(window).trigger('scroll');

    // Gallery Modal
    // Open modal when "view" button is clicked
    $(".view").on("click", function () {
        const imageUrl = $(this).data("image");
        $("#modal .modal_image img").attr("src", imageUrl);
        $("#modal").addClass("active");
        $("html").css("overflow", "hidden");
    });

    // Close modal when "close" button is clicked
    $("#modal .close").on("click", function () {
        $("#modal").removeClass("active");
        $("#modal .modal_image img").attr("src", ""); // Clear image src
        $("html").css("overflow", "");
    });

    // Optional: Close modal when clicking outside the modal container
    $("#modal").on("click", function (e) {
        if ($(e.target).is("#modal")) {
            $(this).removeClass("active");
            $("#modal .modal_image img").attr("src", ""); // Clear image src
            $("html").css("overflow", "");
        }
    });

    $('a[href*="#"]').on('click', function (e) {
        e.preventDefault();
    
        const offset = 200; // Offset for fixed header or spacing
        const target = $($(this).attr('href'));
    
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - offset
            }, 500, 'linear', function () {
                // After scrolling completes, trigger the filter tab logic
                if ($(target).attr('id') === 'articles') {
                    // Add the "active" class or trigger the tab's open functionality
                    $('.filter[data-filter=".articles"]').trigger('click');
                }
            });
        }
    });
});
