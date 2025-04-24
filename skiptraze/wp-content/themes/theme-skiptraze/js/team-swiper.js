var swiper = new Swiper(".team_slider_mobile", {
    slidesPerView: 2,
    slidesPerColumn: 1,
    spaceBetween: 30,
    grabCursor: true,
    navigation: {
    nextEl: '.next',
    prevEl: '.prev',
    },
    effect: 'slide',

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
        slidesPerGroup: 1,
    },
    1000: {
        slidesPerView: 2,
        slidesPerGroup: 1,
    },
    },
});
