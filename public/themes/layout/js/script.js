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
            Talengo.carouselService();
            Talengo.faq();
            Talengo.radioServices();
            Talengo.checkboxService();
            Talengo.openGallery();
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
            if(($('.testimonials_content').length) && Talengo.vars.isMobileView && ($('.testimonials_content').closest('.page-service').length ==0))  {
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
        },

        carouselService : function() {
            var sync1 = $(".main-carousel");
            var sync2 = $(".navigation-thumbs");

            var thumbnailItemClass = '.owl-item';

            // const player = new Plyr('.custom-video', {
            //     autoplay: false,
            //     loop: { active: false },
            //     fullscreen: {enabled: true}
            //   });

            var slides = sync1.owlCarousel({
                video:true,
                items:1,
                loop:false,
                margin:10,
                autoplay:false,
                nav: true,
                dots: false
            }).on('changed.owl.carousel', syncPosition);

            function syncPosition(el) {

                // if (player.playing) {
                //     player.pause();
                // }
                $owl_slider = $(this).data('owl.carousel');
                var loop = $owl_slider.options.loop;

                if(loop){
                    var count = el.item.count-1;
                    var current = Math.round(el.item.index - (el.item.count/2) - .5);
                    if(current < 0) {
                        current = count;
                    }
                    if(current > count) {
                        current = 0;
                    }
                }else{
                    var current = el.item.index;
                }

                var owl_thumbnail = sync2.data('owl.carousel');
                var itemClass = "." + owl_thumbnail.options.itemClass;


                var thumbnailCurrentItem = sync2
                    .find(itemClass)
                    .removeClass("synced")
                    .eq(current);

                thumbnailCurrentItem.addClass('synced');

                if (!thumbnailCurrentItem.hasClass('active')) {
                    var duration = 300;
                    sync2.trigger('to.owl.carousel',[current, duration, true]);
                }
            }

            var thumbs = sync2.owlCarousel({
                autoWidth: true,
                loop:false,
                margin:10,
                autoplay:false,
                nav: false,
                dots: false,
                video:true,
                onInitialized: function (e) {
                    var thumbnailCurrentItem =  $(e.target).find(thumbnailItemClass).eq(this._current);
                    thumbnailCurrentItem.addClass('synced');
                },
            })
                .on('click', thumbnailItemClass, function(e) {
                    e.preventDefault();
                    var duration = 300;
                    var itemIndex =  $(e.target).parents(thumbnailItemClass).index();
                    sync1.trigger('to.owl.carousel',[itemIndex, duration, true]);
                }).on("changed.owl.carousel", function (el) {
                    var number = el.item.index;
                    $owl_slider = sync1.data('owl.carousel');
                    $owl_slider.to(number, 100, true);
                });

            let plyrInstance = null;

            $('.main-carousel').magnificPopup({
                delegate: '.itemGallery',
                type: 'image',
                gallery: {
                    enabled: false
                },
                callbacks: {
                    elementParse: function (item) {
                        const parentItem = item.el.closest('.item');
                        const videoWrapper = parentItem.find('#video-wrapper');

                        if (videoWrapper.length) {
                            item.type = 'inline';
                            item.src = '#' + videoWrapper.attr('id');
                        } else {
                            item.type = 'image';
                            item.src = item.el.attr('src');
                        }
                    },
                    open: function () {
                        $('body').addClass('no-scroll');
                        const current = $.magnificPopup.instance.currItem;
                        if (current.type === 'inline') {
                            const videoEl = document.querySelector(current.src + ' video');
                            if (videoEl) {
                                plyrInstance = new Plyr(videoEl);
                            }
                        }
                    },
                    close: function () {
                        $('body').removeClass('no-scroll');
                        // Stop and destroy Plyr instance
                        if (plyrInstance) {
                            plyrInstance.pause();
                            plyrInstance.stop(); // Important: stop() remet au début
                            plyrInstance.destroy(); // Détruit le player proprement
                            plyrInstance = null;
                        }
                    }
                }
            });


        },

        faq : function () {
            const items = document.querySelectorAll('.accordion button');

            function toggleAccordion() {
                const itemToggle = this.getAttribute('aria-expanded');

                for (i = 0; i < items.length; i++) {
                    items[i].setAttribute('aria-expanded', 'false');
                }

                if (itemToggle == 'false') {
                    this.setAttribute('aria-expanded', 'true');
                }
            }

            items.forEach((item) => item.addEventListener('click', toggleAccordion));
        },

        radioServices : function () {
            $('input[type=radio][name=option-price]').change(function () {
                if ($(this).is('#basic-option') && $(this).is(':checked')) {
                    $('.content-tab').removeClass('custom-op').addClass('basic-op');
                } else {
                    $('.content-tab').removeClass('basic-op').addClass('custom-op');
                }
            });
            $('input[name="option-price"]').each(function () {
                $('label[for="' + $(this).attr('id') + '"]').on('click', function () {
                    const offset = $('.options-price').offset().top - 165;
                    $('html, body').animate({ scrollTop: offset }, 600);
                });
            });


        },

        checkboxService : function () {
            var additional = parseInt($('.basic .nbr').text().trim(), 10);

            var basicPrice =  parseInt($('.options-price .basicprice').text().trim(), 10);

            $('.custom-service input[type="checkbox"]').change(function () {
                const checkbox = $(this);
                const isChecked = checkbox.is(':checked');
                const labelText = checkbox.siblings('label').find('.label').text().trim();
                const checkboxId = checkbox.attr('id');
                const listSelector = '.custom';
                const listNbr = '.custom .nbr';

                const listItemId = 'list-item-' + checkboxId;
                $('#basic-option').prop('checked', true).trigger('change');
                if (isChecked) {

                    if ($('#' + listItemId).length === 0) {
                        $(listSelector).append(
                            `<li id="${listItemId}"><i class="fa-solid fa-check"></i> <span>${labelText}</span></li>`
                        );
                    }
                } else {

                    $('#' + listItemId).remove();
                }



                var val = parseInt($(this).siblings('label').find('.nbr-op').text().trim(), 10);

                var valprice = parseInt($(this).closest('.option').find('.price').text().trim(), 10);


                if ($(this).is(':checked')) {
                    additional += val;
                    basicPrice += valprice;
                } else {
                    additional -= val;
                    basicPrice -= valprice;
                }

                $('.custom .nbr').text(additional);
                $('.priceBtn .price .nbr').text(basicPrice);

                if ($('.custom-service input[type="checkbox"]:checked').length > 0) {
                    $('#custom-option').prop('checked', true).trigger('change');
                    $('#basic-option').prop('checked', false).trigger('change');
                } else {
                    $('#basic-option, #custom-option').prop('checked', false);
                    $('#basic-option').prop('checked', true).trigger('change');
                }

            });
        },

        openGallery: function () {

        }


    }

    Talengo.init();
});

