var swiper = new Swiper(".slider-content", {
    slidesPerView: 4,
    slidesPerColumn: 2,
    spaceBetween: 30,
    grabCursor: true,
    slidesPerColumnFill: 'column',

    pagination: {
        el: '.client-pagination',
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
            slidesPerView: 4,
        },
    },
});