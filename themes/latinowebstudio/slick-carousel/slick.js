$('.slider').slick({
    dots:true,
    prevArrow: '<a class="slick-prev" href="#"><i data-icon="ei-arrow-left" data-size="m"></i></a>',
    nextArrow: '<a class="slick-next" href="#"><i data-icon="ei-arrow-right" data-size="m"></i></a>',
    customPaging: function(slick,index) {
                      var targetImage = slick.$slides.eq(index).find('img').attr('src');
                      return '<img src=" ' + targetImage + ' "/>';
                  }
  });

// alert('hello');
// $('.your-class').slick({
//     slidesToShow: 1,
//     slidesToScroll: 1,
//     arrows: false,
//     fade: true,
//     asNavFor: '.slider-nav'
//   });
//   $('.slider-nav').slick({
//     slidesToShow: 3,
//     slidesToScroll: 1,
//     asNavFor: '.your-class',
//     dots: true,
//     centerMode: true,
//     focusOnSelect: true
//   });