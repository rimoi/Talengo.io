$ = jQuery;
$(function () {
    var Talengo = {

        dom: {
        },

        vars: {
            isDevice: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
            isDeviceIOS: /webOS|iPhone|iPad|iPod/i.test(navigator.userAgent),
            isMobileView: ($(window).width() <= 767) ? true : false,
            isTabletPorView: ($(window).width() <= 992) ? true : false,
            isTabletPayView: ($(window).width() <= 1024) ? true : false
        },

        init: function () {
            Talengo.menuSticky();
            Talengo.testimonialsMobileCarousel();
            Talengo.select2();
            Talengo.menuBurger();
            Talengo.showSearchMobile();
            Talengo.showMore();
            Talengo.carouselProfileTestimonial();
            Talengo.popinPortfolio();
        },

        menuSticky : function () {
            var scrolValue = 0;
            $(window).scroll(function() {
                scrolValue = $(this).scrollTop();
                if(scrolValue > 250 ) {
                    $('header').addClass('stickyMenu');
                } else {
                    $('header').removeClass('stickyMenu');
                }
            })
        },

        testimonialsMobileCarousel :function () {
            if(($('.testimonials_content').length) && Talengo.vars.isMobileView)  {
                $('.testimonials_content').owlCarousel({
                    loop:true,
                    margin:10,
                    nav:false,
                    dots:true,
                    autoHeight:true,
                    items:1,
                    onInitialized: function(event) {
                        $('.owl-dot').each(function(index) {
                            $(this).attr('aria-label', 'Navigate to Slide ' + (index + 1));
                        });
                    }
                })
            }
        },

        select2 : function () {
            if($(".custom-select").length) {
                $('.custom-select').select2();
            }
        },

        menuBurger : function() {
            $('.menuBurger').click( function() {
                $('body').toggleClass('activeMenuBurger');
            });
        },

        showSearchMobile : function() {
            $('.iconSearchMobile').click( function() {
                $('header').toggleClass('openSearch');
            })
        },

        showMore : function () {
            if($('.aboutme .description').length) {
                var original = $('.aboutme .description');
                var textHeight = $('.aboutme .description').height();
                var button = $('.aboutme .show-more');
                var buttonText = $('.aboutme .show-more span');

                var $clone = original.clone();
                $clone.css({
                    position: 'absolute',
                    visibility: 'hidden',
                    height: 'auto',
                    overflow: 'visible',
                    display: 'block',
                    '-webkit-line-clamp': 'unset',
                    '-webkit-box-orient': 'initial'
                });
                $('body .aboutme').append($clone);

                var fullHeight = $clone.height();

                $clone.remove();

                if (fullHeight > 162) {
                    button.show();
                }

                button.on('click', function () {
                    original.toggleClass('expanded');
                    buttonText.text(original.hasClass('expanded') ? 'Réduire' : 'Lire la suite');
                });
            }
        },

        carouselProfileTestimonial : function () {
            if($('.profilSection .testimonials_content').length) {

                $('.profilSection .testimonials_content').owlCarousel({
                    loop:false,
                    margin:10,
                    dots:false,
                    responsive:{
                        0:{
                            items:1,
                            nav:false,
                        },
                        991 : {
                            items:2,
                            nav:true,
                        },
                        1024 :{
                            items:3,
                            nav:true,
                        },
                        1251:{
                            items:4,
                            nav:true,
                        },
                    }
                })
            }
        },

        popinPortfolio : function () {
            if($('.open-popup-link').length) {

                var scrollY = 0;

                $('.open-popup-link').on('click', function(e) {
                    e.preventDefault();

                    var popupId = $(this).data('popup-id');
                    var $popup = $('#' + popupId);

                    if ($popup.length) {
                        scrollY = $(window).scrollTop();
                        $('body').addClass('no-scroll')
                        $popup.addClass('active');
                    }
                });

                $('.popup-close').on('click', function() {
                    $('body').removeClass('no-scroll').css('top', '');
                    $(window).scrollTop(scrollY);
                    var $popup = $(this).closest('.content-popup');
                    $popup.removeClass('active');
                });

                $('.content-popup').on('click', function(e) {
                    if (e.target === this) {
                        $('body').removeClass('no-scroll').css('top', '');
                        $(window).scrollTop(scrollY);
                        $(this).removeClass('active');
                    }
                });

            }
        }

    }

    Talengo.init();
});

