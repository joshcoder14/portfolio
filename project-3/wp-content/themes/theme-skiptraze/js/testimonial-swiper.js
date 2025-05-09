var swiper = new Swiper(".slide-content", {
    slidesPerView: 3,
    slidesPerGroup: 1,
    spaceBetween: 30,
    speed: 1000,
    grabCursor: true,
    fade: true,
    autoplay: {
        delay: 3000,
    },
    pagination: {
      el: ".testimonial-pagination",
      clickable: true,
    },

    breakpoints: {
        0: {
            slidesPerView: 1,
            slidesPerGroup: 1,
        },
        375: {
            slidesPerView: 1,
            slidesPerGroup: 1,
        },
        640: {
            slidesPerView: 2,
            slidesPerGroup: 2,
        },
        1000: {
            slidesPerView: 3,
        },
    },
});