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
            Talengo.changeTopService();
            Talengo.checkboxService();
            Talengo.stripe();
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
            if (
                $('.testimonials_content').length
                && Talengo.vars.isMobileView
                && $('.testimonials_content').closest('.page-service').length == 0
                && $('.testimonials_content').data('confirme') == "non") {
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


        changeTopService : function() {
            if ($('.custom-select').length) {
                $('.custom-select').on('change', function (e) {
                    let param = $(this).val();
                    let path = $(this).data('url');
                    window.location.href = path + '?q=' + param
                });
            }
        },

        checkboxService: function () {
            let additional = parseInt($('.basic .nbr').text().trim(), 10);
            let basicPrice = parseInt($('.options-price .basicprice').text().trim(), 10);

            // Nettoie les anciens écouteurs pour éviter les doublons
            $('.custom-service input[type="checkbox"]').off('change').on('change', function () {
                const $checkbox = $(this);
                const isChecked = $checkbox.is(':checked');
                const checkboxId = $checkbox.attr('id');
                const labelText = $checkbox.siblings('label').find('.label').text().trim();
                const val = parseInt($checkbox.siblings('label').find('.nbr-op').text().trim(), 10);
                const valPrice = parseInt($checkbox.closest('.option').find('.price').text().trim(), 10);

                const listItemId = `list-item-${checkboxId}`;
                const $customList = $('.custom');

                // Met à jour les totaux
                if (isChecked) {
                    additional += val;
                    basicPrice += valPrice;

                    // Ajouter l'élément personnalisé s'il n'existe pas
                    if (document.getElementById(listItemId) === null) {
                        $customList.append(
                            `<li id="${listItemId}"><i class="fa-solid fa-check"></i> <span>${labelText}</span></li>`
                        );
                    }
                } else {
                    additional -= val;
                    basicPrice -= valPrice;

                    // Supprimer l'élément personnalisé s'il existe
                    document.getElementById(listItemId)?.remove();
                }

                // Met à jour les compteurs
                $('.custom .nbr').text(additional);
                $('.priceBtn .price .nbr').text(basicPrice);
                $('.priceBtn .js-total').val(basicPrice);

                // Activer/désactiver les onglets
                const hasCustom = $('.custom-service input[type="checkbox"]:checked').length > 0;

                $('#custom-option').prop('checked', hasCustom).trigger('change');
                $('#basic-option').prop('checked', !hasCustom).trigger('change');
            });
        },

        stripe : function () {
            if ($('#stripe-token').length) {



                    const publicKey = $('.card-form').data('publicKey');
                    const stripe = Stripe(publicKey);
                    const elements = stripe.elements({
                        appearance: {
                            theme: 'flat',
                            variables: {
                                colorPrimary: '#f06292',
                                colorBackground: '#fbe9e7',
                                colorText: '#4a148c',
                                colorDanger: '#ff5252',
                                fontFamily: '"Comic Sans MS", cursive, sans-serif',
                                spacingUnit: '4px',
                                borderRadius: '4px'
                            },
                            rules: {
                                '.Input': {
                                    border: '1px solid #f06292',
                                    boxShadow: 'none'
                                }
                            }
                        }
                    });
                    const cardElement = elements.create('card', {
                        hidePostalCode: true,
                        style: {
                            base: {
                                iconColor: '#f06292',
                                fontWeight: '500'
                            }
                        }
                    });
                    cardElement.mount('#card-element');

                    $('form.payment-methods').on('submit', function (e) {
                        const selectedPayment = $('input[name="payment"]:checked').val();

                        if (selectedPayment === 'card') {
                            e.preventDefault(); // Bloque la soumission

                            stripe.createToken(cardElement).then(function (result) {
                                if (result.error) {
                                    alert(result.error.message);
                                } else {
                                    $('#stripe-token').val(result.token.id);
                                    e.currentTarget.submit(); // Soumets le formulaire avec le token
                                }
                            });

                        } // Sinon (PayPal), le formulaire se soumet normalement
                    });
                }
        },
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

