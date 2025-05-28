$ = jQuery;

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
    },

    menuSticky : function () {
        var scrolValue = 0;

        if (!$('body.noStickyMenu').length) {
            $(window).scroll(function() {
                scrolValue = $(this).scrollTop();
                if(scrolValue > 250 ) {
                    $('header').addClass('stickyMenu');
                } else {
                    $('header').removeClass('stickyMenu');
                }
            })
        }
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
    }


}
$(function () {
    Talengo.init();
});

