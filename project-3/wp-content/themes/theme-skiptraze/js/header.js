jQuery(document).ready(function ($) {
    "use strict";

    // Header
    const nav_header = document.querySelector(".header");
    const mobile_menu = document.querySelector(".mobile_menu");
  
    const toggleNavbar = () => {
        nav_header.classList.toggle("mobile-menu-open");
    }

    if (mobile_menu) {
        mobile_menu.addEventListener("click", () => toggleNavbar());
    }

    $(window).scroll(function(){
        $("header").toggleClass("sticky-header", $(this).scrollTop() > 50);
    });

    $("header").toggleClass("sticky-header", $(this).scrollTop() > 50);
    //end of header

    // $('.menu .menu-item ').click(function() {
    //     $('.menu .menu-item').removeClass('current-menu');
    //     $('.menu .menu-item').addClass('current-menu');
    // });

    // Language Dropdown
    $(".list").click(function() {
        $(".list").removeClass("current-lang");
        $(this).addClass("current-lang");
        $(".dropdown_content").removeClass("show");
    });

    $(".btn_dropdown").click(function() {
        $(".dropdown_content").addClass("show");
    });

    $(document).click(function(event) {
        var dropdown = $(".dropdown");
        if (!dropdown.is(event.target) && dropdown.has(event.target).length === 0) {
            $(".dropdown_content").removeClass("show");
        }
    });
    // End Language Dropdown
})