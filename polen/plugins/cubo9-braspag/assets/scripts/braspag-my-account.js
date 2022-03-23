(function($) {
    $(document).ready(function() {
        if( $("input#braspag_creditcardValidity").length > 0 ){
            $("input#braspag_creditcardValidity").inputmask({
                mask: ['99 / 9999'],
                keepStatic: true
            });
        }

        $(document).on( 'click', '.braspag-make-default-payment', function(e) {
            e.preventDefault();
            let myChild = $(this).children();
            let default_id = $(this).attr('default-id');

            braspagMakeDefault( default_id );

            $('#cards-accordion').find('.braspag-make-default-payment', function() {
                let brandName = $(this).attr('brand-name');
                let default_id = $(this).attr('default-id');
                $('#braspag-brand-name-' + default_id).html(brandName);
                if( $(this).hasClass( 'glyphicon-star' ) ) {
                    $(this).removeClass( 'glyphicon-star' ).addClass( 'glyphicon-ok' );
                }
            });

            if( myChild.hasClass( 'glyphicon-ok' ) ) {
                myChild.removeClass( 'glyphicon-ok' ).addClass( 'glyphicon-star' );
                let newBrandLabel = $( '#braspag-brand-name-' + default_id ).html() + ' (Padrão)';
                $('#braspag-brand-name-' + default_id ).html(newBrandLabel);
            } else if( myChild.hasClass( 'glyphicon-star' ) ) {
                myChild.removeClass( 'glyphicon-star' ).addClass( 'glyphicon-ok' );
            }
        });

        $(document).on( 'click', '.braspag-remove-payment', function(e) {
            e.preventDefault();
            if (!confirm("Tem certeza que deseja excluir esse cartão?")) {
                return;
            }
            braspagRemove( $(this).attr('remove-id') );
            let remove_id = document.getElementById('#payment-' + $(this).attr('remove-id'));
            remove_id.parentNode.removeChild(remove_id);
        });

        $(document).on( 'click', '#braspag-save-my-card', function(e) {
            let braspag_creditcardNumber   = $('#braspag_creditcardNumber').val();
            let braspag_creditcardName     = $('#braspag_creditcardName').val();
            let braspag_creditcardValidity = $('#braspag_creditcardValidity').val();
            let braspag_creditcardCvv      = $('#braspag_creditcardCvv').val();
            
            if (braspag_creditcardNumber 
            && braspag_creditcardName 
            && braspag_creditcardValidity 
            && braspag_creditcardCvv) {
                e.preventDefault();
                blockUnblockInputs("#form-add-card", true);
                $.ajax({
                    url: braspag.ajaxUrl,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action'   : 'braspag-add-card',
                        'number'   : braspag_creditcardNumber,
                        'holder'   : braspag_creditcardName,
                        'validity' : braspag_creditcardValidity,
                        'cvv'      : braspag_creditcardCvv,
                    },
                    beforeSend: function() {
                        polSpinner();
                    },
                    success: function( response ) {
                        polSpinner("hidden");
                        if(!response) {
                            polError("Erro ao validar seu cartão. Aguarde alguns instantes e tente novamente");
                            return;
                        }
                        try {
                            console.log( response.result );
                            if( response.result === 'success' ) {
                                document.location.href = braspag.myAccountPaymentOptionUrl;
                            } else {
                                polError(response.message);
                                console.log( response.message );
                            }
                        } catch(error) {
                            polError(error);
                        }
                    },
                    error: function( error ) {
                        polSpinner("hidden");
                        polError(error);
                        console.log( error );
                    },
                    complete: function() {
                        blockUnblockInputs("#form-add-card", false);
                    }
                });
            }
        });
    });

    function braspagMakeDefault( default_id ) {
        $.ajax({
            url: braspag.ajaxUrl,
            method: 'post',
            dataType: 'json',
            data: {
                'id': default_id,
                'action': 'braspag-default',
            },
            beforeSend: function() {
                $( '.braspag-make-default-payment' ).unbind( 'click' );
            },
            success: function( data ) {
                console.log( data );
            },
            error: function( error ) {
                console.log( error );
            }
        }).done(function() {
            $( '.braspag-make-default-payment' ).bind( 'click' );
        });
    }

    function braspagRemove( default_id ) {
        polSpinner();
        $.ajax({
            url: braspag.ajaxUrl,
            method: 'post',
            dataType: 'json',
            data: {
                'id': default_id,
                'action': 'braspag-remove',
            },
            beforeSend: function() {
                $( '.braspag-remove-payment' ).unbind( 'click' );
            },
            success: function( data ) {
                console.log( data );
                polSpinner("hidden");
                if( $( '.payment-method-item' ).length === 0 ) {
                    window.location.reload();
                }
            },
            error: function( error ) {
                polSpinner("hidden");
                console.log( error );
            }
        }).done(function() {
            $( '.braspag-remove-payment' ).bind( 'click' );
        });
    }
})(jQuery);