$('.products-carousel').owlCarousel({
    // center: true,
    loop: false,
    margin: 10,
    nav: true,
    dots: false,
    // autoHeight: false,
    // autoHeightClass: 'owl-height',
    // stagePadding:170,
    autoplay: false,
    autoplayTimeout: 2500,
    autoplaySpeed: 5000, // this seems to make it autoscroll
    autoplayHoverPause: false,
    animateIn: 'fadeIn',
    animateOut: 'fadeOut',
    // navText : ["<img src='/wp-content/uploads/2021/07/Arrow-Left-Blair-ITC.png' />","<img src='/wp-content/uploads/2021/07/Arrow-Right-Blair-ITC.png' />"],
    responsive: {
        0: {
            items: 2,
            // slideBy: 2
        },
        600: {
            items: 3,
            // slideBy: 3
        },
        1000: {
            items: 4,
            // slideBy: 4
        }
    }
});