jQuery(document).ready(function ($) {
  "use strict";

  const $window = $(window);

  // Elements to animate
  const elementsToAnimate = $('.heading, .accordion, .list, .lender_card, .footer_logo, .contact, .terms_and_policies');

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
  $window.on('scroll', function() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(function() {
      if ($window.scrollTop() > 10) {
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
  $window.trigger('scroll');

  $(".accordion_header").on("click", function(){
    // self clicking close
    if($(this).next(".accordion_body").hasClass("active")){
      $(this).next(".accordion_body").removeClass("active").slideUp();
      $(this).children(".icon").removeClass("close").addClass("open");
    }
    else{
      $(".accordion_card .accordion_body").removeClass("active").slideUp();
      $(".accordion_card .accordion_header .icon").removeClass("close").addClass("open");
      $(this).next(".accordion_body").addClass("active").slideDown();
      $(this).children(".icon").removeClass("open").addClass("close");
    }
  });

});