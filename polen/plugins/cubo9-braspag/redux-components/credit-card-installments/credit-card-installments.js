/*global redux_change, redux*/

(function( $ ) {
	'use strict';

	redux.field_objects            = redux.field_objects || {};
	redux.field_objects.credit_card_installments = redux.field_objects.credit_card_installments || {};

	redux.field_objects.credit_card_installments.remove = function( el ) {
		$(document).on( 'click', '.redux-credit-card-installments-remove', function() {
			// $( this ).parent().parent().parent().parent().next('.redux-credit-card-table-installments').remove();
			$( this ).parent().parent().parent().parent().parent().remove();
		});
	};

	redux.field_objects.credit_card_installments.init = function( selector ) {
		if ( ! selector ) {
			selector = $( document ).find( '.redux-container-credit_card_installments:visible' );
		}

		$( selector ).each(
			function() {
				var el     = $( this );
				var parent = el;

				if ( ! el.hasClass( 'redux-field-container' ) ) {
					parent = el.parents( '.redux-field-container:first' );
				}

				if ( parent.is( ':hidden' ) ) {
					return;
				}

				if ( parent.hasClass( 'redux-field-init' ) ) {
					parent.removeClass( 'redux-field-init' );
				} else {
					return;
				}

				redux.field_objects.credit_card_installments.remove( el );

				el.find( '.redux-credit-card-installments-add' ).click( function() {
					let html = '<div><table class="redux-credit-card-installments">';
					html += $('.redux-credit-card-installments-base-table').html();
					html += '</table>';
					html += '<table class="redux-credit-card-installments">';
					html += $('.redux-credit-card-installments-base-table-installments').html();
					html += '</table>';
					html += '<hr></div>';
					$('#creditCardBrandsInstallments').append( html );
				});
			}
		);
	};
})( jQuery );
