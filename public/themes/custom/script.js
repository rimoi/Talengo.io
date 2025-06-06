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
            Talengo.showSearchMobile();
            Talengo.changeTopService();
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

        showSearchMobile : function() {
            $('.iconSearchMobile').click( function() {
                $('header').toggleClass('openSearch');
            })
        },

        changeTopService : function() {
            if ($('.custom-select').length) {
                $('.custom-select').on('change', function (e) {
                    let param = $(this).val();
                    let path = $(this).data('url');
                    window.location.href = path + '?q=' + param
                });
            }
        }

    }

    Talengo.init();


    function onClickBtnFavoris(event) {
        event.preventDefault();

        const url = this.href;
        const icone = this.querySelector('i')

        console.log(icone);

        axios.get(url)
            .then(function (response) {
                // handle success
                //const favoris = response.data.favoris;
                if (icone.classList.contains('green')) {
                    icone.classList.replace('green', 'light');
                }
                else {
                    icone.classList.replace('light', 'green');
                }

                console.log(response);
            })
            .catch(function (error) {
                if (error.status === 403) {
                    window.alert("Vous ne pouvez pas  ajouter un microservice en favoris sans pour autant vous connecter")
                } else {
                    window.alert("Une erreur s'est produite lors de la requette veuillez resayer plus tard")
                }
                console.log(error);
            })
            .finally(function () {
                // always executed
            });
    }

    document.querySelectorAll('a.js-favoris').forEach(function (link) {

        link.addEventListener('click', onClickBtnFavoris)

    })
});

