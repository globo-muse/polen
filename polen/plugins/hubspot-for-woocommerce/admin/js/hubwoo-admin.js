( function( $ ) {
	'use strict';

	// i18n variables
	const ajaxUrl                      = hubwooi18n.ajaxUrl;
	const hubwooWentWrong              = hubwooi18n.hubwooWentWrong;
	const hubwooMailSuccess            = hubwooi18n.hubwooMailSuccess;
	const hubwooMailFailure            = hubwooi18n.hubwooMailFailure;
	const hubwooSecurity               = hubwooi18n.hubwooSecurity;
	const hubwooAccountSwitch          = hubwooi18n.hubwooAccountSwitch;
	const hubwooOcsSuccess             = hubwooi18n.hubwooOcsSuccess;
	const hubwooOcsError               = hubwooi18n.hubwooOcsError;
	const hubwooOverviewTab            = hubwooi18n.hubwooOverviewTab;
	const dataCounter                  = {
		percentage: 0,
		totalCount: 0,
		totalGrpPrCreated: 0,
		setupRunning: false
	};

	jQuery( document ).ready(
		function() {
			const createLists          = ( list ) => {
				return new Promise(
					( resolve, reject ) => {
                    const listData = {
							action: 'hubwoo_create_list',
							listDetails: list,
							hubwooSecurity,
						};
                    jQuery.ajax( { url: ajaxUrl, type: 'POST', data: listData } )
						.then(
							( listResponse ) => {
                            if ( listResponse != null ) {
                                const response     = jQuery.parseJSON( listResponse );
                                const responseCode = response.status_code;
                                if ( responseCode == 200 || responseCode == 409 ) {
                                    dataCounter.totalGrpPrCreated += 1;
                                    dataCounter.percentage         = ( ( dataCounter.totalGrpPrCreated / dataCounter.totalCount ) * 100 ).toFixed( 0 );
                                    updateProgressBar( dataCounter.percentage );
                                    resolve( response );
                                } else {
                                    reject( response );
                                }
                            }
							},
						);
					},
				);
			};

			const createGroupsToHS = ( groupName, type ) => {
				let action         = '';

				if ( type == 'contact' ) {
					action = 'hubwoo_create_property_group';
				} else if ( type == 'deal' ) {
					action = 'hubwoo_deals_create_group';
				}

				const groupData = {
					action,
					createNow: 'group',
					groupName,
					hubwooSecurity,
				};

				return new Promise(
					( resolve, reject ) => {
                    jQuery.ajax( { url: ajaxUrl, type: 'POST', data: groupData } )
						.done(
							( groupResponse ) => {
                                const groupRes = jQuery.parseJSON( groupResponse );
                                groupRes.name  = groupName;
                                if ( groupRes.status_code == 200 || groupRes.status_code == 409 ) {
                                    dataCounter.totalGrpPrCreated += 1;
                                    dataCounter.percentage         = ( ( dataCounter.totalGrpPrCreated / dataCounter.totalCount ) * 100 ).toFixed( 0 );
                                    updateProgressBar( dataCounter.percentage );
                                    resolve( groupRes );
                                } else {
                                	reject( groupRes );
                                }
							},
						);
					},
				);
			};

			const createPropertiesToHS = ( dealProperty, type ) => {
				let action             = '';
				if ( type == 'contact' ) {
					action = 'hubwoo_create_group_property';
				} else if ( type == 'deal' ) {
					action = 'hubwoo_deals_create_property';
				}

				return new Promise(
					( resolve, reject ) => {
                    const createProperties = {
							action,
							propertyDetails: dealProperty,
							hubwooSecurity,
						};
                    jQuery.ajax( { url: ajaxUrl, type: 'POST', data: createProperties } )
						.done(
							function( propertyResponse ) {
								const propRes = jQuery.parseJSON( propertyResponse );

								if ( propRes.status_code == 201 ) {
									dataCounter.totalGrpPrCreated += propRes.body.results.length;
									dataCounter.percentage         = ( ( dataCounter.totalGrpPrCreated / dataCounter.totalCount ) * 100 ).toFixed( 0 );
									updateProgressBar( dataCounter.percentage );
									resolve( propRes );
								} else if ( propRes.status_code == 207 ) {
									dataCounter.totalGrpPrCreated += propRes.body.numErrors;
									dataCounter.percentage         = ( ( dataCounter.totalGrpPrCreated / dataCounter.totalCount ) * 100 ).toFixed( 0 );
									updateProgressBar( dataCounter.percentage );
									resolve( propRes );
								} else {
									reject( propRes );
								}
							},
						);
					},
				);
			};

			const debounce = ( func, delay ) => {
				let callDebounce;

				return function() {
					const context = this;
					const args    = arguments;
					clearTimeout( callDebounce );
					callDebounce = setTimeout( () => func.apply( context, args ), delay );
				};
			};

			const trackInputCheckboxes = () => {
				return {
					hubwoo_customers_manual_sync: '.hubwoo-date-range',
					hubwoo_abncart_delete_old_data: '.delete-after-date',
					hubwoo_checkout_optin_enable: '#hubwoo_checkout_optin_label',
					hubwoo_registeration_optin_enable: '#hubwoo_registeration_optin_label',
					hubwoo_ecomm_order_date_allow: '.hubwoo-date-d-range',
				};
			};

			const arrayChunk    = ( array, size ) => {
				const tempArray = [];

				const length     = array.length;
				let initialIndex = 0,
					maxLength    = Math.round( length / size ),
					lastIndex    = size;

				for ( let index = 0; index < maxLength + 1; index += 1 ) {
					const cutArr = array.slice( initialIndex, lastIndex );
					if ( cutArr.length > 0 ) {
						tempArray.push( cutArr );
					}
					initialIndex = lastIndex;
					lastIndex   += size;
				}

				return tempArray;
			};

			const runEcommSetup = async () => {
				let response = await jQuery.ajax( { url: ajaxUrl, type: 'POST', data: { action: 'hubwoo_ecomm_setup', hubwooSecurity, process: 'upsert-store' } } );
				response     = jQuery.parseJSON( response );
				if ( response.status_code == 200 ) {
					const storeId       = response.body.id;
					const totalProducts = jQuery( '.hubwoo-info' ).data( 'products' );
					await saveUpdates( { 'hubwoo_ecomm_store_id': storeId } );
					await jQuery.ajax( { url: ajaxUrl, type: 'POST', data: { action: 'hubwoo_ecomm_setup', hubwooSecurity, process: 'start-products-sync' } } );
					if(dataCounter.setupRunning) { updateProgressBar( 95 ) }
					await jQuery.ajax( { url: ajaxUrl, type: 'POST', data: { action: 'hubwoo_ecomm_setup', hubwooSecurity, process: 'update-deal-stages' } } );
				}
				return response				
			}


			const manageSpinText = ( job, type, result = 'none' ) => {
				if ( type == 'add' ) {
					job.addClass( 'fa' );
					job.removeClass( 'hubwoo-cr-btn' );
					job.removeClass( 'hubwoo-crd-btn' );
					job.addClass( 'fa-spinner' );
					job.addClass( 'fa-spin' );
					job.text( '' );
				} else if ( type == 'remove' ) {
					job.removeClass( 'fa' );
					job.removeClass( 'fa-spinner' );
					job.removeClass( 'fa-spin' );
					job.addClass( 'hubwoo-cr-btn' );
					job.text( 'Created' );

					if ( result == 'failed' ) {
						job.addClass( 'hubwoo-crd-btn' );
						job.text( 'Create' );
					}
				}
			};

			const updateProgressBar = ( percentage, mode = 1 ) => {
				if ( mode == 1 ) {
					jQuery( '.hubwoo-progress-bar' ).css( 'width', percentage + '%' );
					jQuery( '.hubwoo-progress-bar' ).html( percentage + '%' );
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsSuccess );
				} else if ( mode == 2 ) {
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
					jQuery( '.hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
					jQuery( '.hubwoo-progress-bar' ).css( 'width', '100%' );
					jQuery( '.hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );
				}
			};

			const capitalize = (s) => {
			  if (typeof s !== 'string') return ''
			  return s.charAt(0).toUpperCase() + s.slice(1)
			}

			const transferScreen = async( screenKey, lastKey = undefined ) => {
				let redirectUrl,
					redirect     = true,
					currentPage  = window.location.href;

				if ( lastKey == undefined ) {
					lastKey = 'hubwoo_key';
				}

				if ( ! currentPage.includes( lastKey ) ) {
					currentPage = jQuery( '.mwb-heb__nav-list-item:eq(0) a' ).attr( 'href' );
				}
				redirectUrl  = currentPage.substring( 0, currentPage.indexOf( lastKey ) );
				redirectUrl += lastKey;

				switch ( screenKey ) {
					case 'move-to-grp-pr':
						redirectUrl += '=grp-pr-setup';
					break;
					case 'move-to-list':
						redirectUrl += '=list-setup';
					break;
					case 'move-to-sync':
						redirectUrl += '=sync';
					break;
					case 'greet-to-dashboard':
						await saveUpdates( { 'hubwoo_connection_setup_established': 1 } );
						redirectUrl += '=hubwoo-overview';
					break;
					case 'move-to-dashboard':
						redirectUrl += '=hubwoo-overview';
					break;
					case 'skip-list-creation':
						jQuery( '.hubwoo_pop_up_wrap' ).slideUp( 800 );
						redirect = lastKey != 'hubwoo_key'
						? false
						: 'true' == await saveUpdates( { 'hubwoo_pro_lists_setup_completed': 1 } )
							? true
							: false;
					break;
					default:
						location.reload();
				}

				if ( redirect ) {
					window.location.href = redirectUrl;
				}
			};

			const getCurrentUsersToSync = () => {
				jQuery.post(
					ajaxUrl,
					{ action: 'hubwoo_get_user_for_current_roles', hubwooSecurity },
					function( totalUsers ) {
						let message = 'We have found ' + totalUsers + ' Users';

						if ( jQuery( '#hubwoo_customers_manual_sync' ).is( ':checked' ) ) {
							message += ` from ${ jQuery( '#hubwoo_users_from_date' ).val() } to ${ jQuery( '#hubwoo_users_upto_date' ).val() }`;
						}
						jQuery('#hubwoo-usr-spin').hide()
						jQuery( '.hubwoo-ocs-btn-notice' ).text( message + ' ready to be synced over HubSpot' );
						if ( totalUsers > 0 ) {
							jQuery( '.hubwoo-osc-instant-sync' ).attr( 'data-total_users', totalUsers );
							jQuery( '#hubwoo-osc-instant-sync' ).fadeIn();
							jQuery( '#hubwoo-osc-schedule-sync' ).hide();							
						} else {
							jQuery( '.hubwoo-ocs-btn-notice' ).delay( 1500 ).slideDown( 5000 );
							jQuery( '.hubwoo-ocs-btn-notice' ).text( 'We could not find any user / order, please try changing the filters' );
							jQuery( '#hubwoo-osc-instant-sync' ).hide();
							jQuery( '#hubwoo-osc-schedule-sync' ).hide();
						}

						if ( totalUsers > 500 ) {
							jQuery( '#hubwoo-osc-schedule-sync' ).fadeIn();
							jQuery( '#hubwoo-osc-instant-sync' ).hide();
						}

						const userRoles = jQuery( '#hubwoo_customers_role_settings' ).val();

						if ( userRoles === undefined || userRoles.length === 0 ) {
							jQuery( '.hubwoo-ocs-btn-notice' ).hide();
							jQuery( '#hubwoo-osc-instant-sync' ).hide();
							jQuery( '#hubwoo-osc-schedule-sync' ).hide();
						}
					},
				);
			};
			const startContactSync      = async( step, progress ) => {
				const response          = await jQuery.ajax(
					{
						type: 'POST',
						url: ajaxUrl,
						data: {
							action: 'hubwoo_ocs_instant_sync',
							step,
							hubwooSecurity,
						},
						dataType: 'json',
					},
				).fail(
					( response ) => {
                    updateProgressBar( response.progress, 2 );
                    saveUpdates( [ 'hubwoo_total_ocs_need_sync' ], 'delete' );
					},
				);

				if ( 100 == response.progress && response.propertyError != true ) {
					updateProgressBar( response.progress );
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsSuccess );
					jQuery( 'a#hubwoo-osc-instant-sync' ).hide();
					await saveUpdates( { 'hubwoo_greeting_displayed_setup': 'yes' } );
					location.reload();
				} else if ( response.propertyError == true ) {
					updateProgressBar( 100, 2 );
					saveUpdates( [ 'hubwoo_total_ocs_need_sync' ], 'delete' );
				} else {
					updateProgressBar( Math.ceil( response.progress ) );
					startContactSync( parseInt( response.step ), parseInt( response.progress ) );
				}
			};

			const getDealsUsersToSync = async() => {
				const ocsCount        = await jQuery.post( ajaxUrl, { action: 'hubwoo_ecomm_get_ocs_count', hubwooSecurity } );

				let message = 'No Users found. Please change the filters below.';

				if ( ocsCount > 0 ) {
					message = `We have found ${ ocsCount } Orders for the selected order statuses`;
					jQuery( '.manage_deals_ocs' ).fadeIn();
				} else {
					jQuery( '.manage_deals_ocs' ).hide();
					jQuery( '.deal-sync_progress' ).hide();
					message = 'No Orders found for the selected order statuses';
				}
				if(jQuery('#hubwoo_ecomm_order_date_allow').is(':checked')) {
					message += ' and date range'
				}

				jQuery( '.hubwoo_deals_message[data-sync-type="order"]' ).text( message );
				jQuery( '.hubwoo-group-wrap__deal_notice[data-type="pBar"]' ).slideDown( 'slow' );
			};

			const saveUpdates = ( data, action = 'update' ) => {
				return jQuery.post( ajaxUrl, { action: 'hubwoo_save_updates', hubwooSecurity, updates: data, type: action } );
			};

			const prepareFormData      = ( data, enableKeys = null, singleKeys = false ) => {
				const preparedFormData = {};

				data.map(
					( formElement ) => {
                    let { name, value } = formElement;
                    if ( enableKeys != null ) {
                        enableKeys.map(
                        ( enableKey ) => {
                            if ( name == enableKey.key ) {
                                enableKey.status = 'yes';
                            }
                            },
                        );
                    }
                    if ( name.includes( '[]' ) == false && singleKeys ) {
                        preparedFormData[ name ] = value;
                    }
                    if ( name.includes( '[]' ) && value != '' ) {
                        name = name.replace( '[]', '' );

                        if ( preparedFormData.hasOwnProperty( name ) ) {
                            preparedFormData[ name ].push( value );
                        } else {
                            preparedFormData[ name ] = [ value ];
                        }
                    }
					},
				);

			if ( enableKeys !== null ) {
				enableKeys.map(
					( enableKey ) => {
                    preparedFormData[ enableKey.key ] = enableKey.status;
					},
				);
			}

				return preparedFormData;
			};

			Object.entries( trackInputCheckboxes() ).map(
				( elementData ) => {
	                const [ key, hideField ] = elementData;
	                if ( jQuery( `#${ key }` ).is( ':checked' ) ) {
	                    jQuery( '.hubwoo-date-range' ).closest( 'tr' ).slideDown();
	                } else {
	                	jQuery( hideField ).closest( 'tr' ).hide();
	                }
				}
			);

			jQuery( '#hubwoo_pro_switch' ).on(
				'click',
				function( e ) {
					if ( ! confirm( hubwooAccountSwitch ) ) {
						e.preventDefault();
						return false;
					}
				},
			);

			if ( jQuery( '#get_workflow_scope' ).val() == 'false' ) {
				setTimeout(
					function() {
						jQuery( '.hubwoo_pop_up_wrap' ).slideDown( 'slow' );
					},
					1000
				);
			}
			const dataOfSelected = jQuery( '#hubwoo_customers_role_settings' ).val();
			if ( dataOfSelected != null ) {
				jQuery.post(
					ajaxUrl,
					{ action: 'hubwoo_get_current_sync_status', hubwooSecurity, data: { type: 'contact' } },
					function( response ) {
						// response = jQuery.parseJSON( response );
						if ( response === true || response == 1 ) {
							jQuery( '#hubwoo_customers_role_settings' ).attr( { disabled: 'true' } );
							jQuery( '#hubwoo_customers_manual_sync' ).attr( { disabled: 'true' } );
						} else {
							getCurrentUsersToSync();
						}
					},
				);
			}

			const innerWidth = jQuery( '.hubwoo-form-wizard-wrapper' ).find( '.hubwoo-form-wizard-link' ).innerWidth();
			const position   = jQuery( '.hubwoo-form-wizard-wrapper' ).find( '.hubwoo-form-wizard-link' ).position();
			if ( position != null ) {
				jQuery( '.hubwoo-form-wizardmove-button' ).css( { left: position.left, width: innerWidth } );
			}

			if( new URLSearchParams(window.location.search).get('trn') == 'shDls') {
			   	window.scroll( {
					top: 200,
					behavior: 'smooth'
			   	} );
				jQuery('.hubwoo-group-wrap__deal_ocs[data-txn="ocs-form"]').slideDown()
			}

			jQuery( '.hubwoo-form-wizard-wrapper' ).find( '.hubwoo-form-wizard-link' ).click(
				function() {
					jQuery( '.hubwoo-form-wizard-link' ).removeClass( 'active' );
					const innerWidth = jQuery( this ).innerWidth();
					jQuery( this ).addClass( 'active' );
					const position = jQuery( this ).position();
					jQuery( '.hubwoo-form-wizardmove-button' ).css( { left: position.left, width: innerWidth } );
					const attr = jQuery( this ).attr( 'data-attr' );
					jQuery( '.hubwoo-form-wizard-content' ).each(
						function() {
							if ( jQuery( this ).attr( 'data-tab-content' ) == attr ) {
								jQuery( this ).addClass( 'show' );
							} else {
								jQuery( this ).removeClass( 'show' );
							}
						},
					);
				},
			);

			jQuery( '.hubwoo-form-wizard-next-btn' ).click(
				function() {
					const next = jQuery( this );
					next.parents( '.hubwoo-form-wizard-content' ).toggle( 'slow' );
					next.parents( '.hubwoo-form-wizard-content' ).next( '.hubwoo-form-wizard-content' ).toggle( 'slow' );
					jQuery( document ).find( '.hubwoo-form-wizard-content' ).each(
						function() {
							if ( jQuery( this ).hasClass( 'show' ) ) {
								const formAtrr = jQuery( this ).attr( 'data-tab-content' );
								jQuery( document ).find( '.hubwoo-form-wizard-wrapper li a' ).each(
									function() {
										if ( jQuery( this ).attr( 'data-attr' ) == formAtrr ) {
											jQuery( this ).addClass( 'active' );
											const innerWidth = jQuery( this ).innerWidth();
											const position   = jQuery( this ).position();
											jQuery( document ).find( '.hubwoo-form-wizardmove-button' ).css( { left: position.left, width: innerWidth } );
										} else {
											jQuery( this ).removeClass( 'active' );
										}
									},
								);
							}
						},
					);
				},
			);

			jQuery( '.hubwoo-form-wizard-previous-btn' ).click(
				function() {
					const prev = jQuery( this );
					prev.parents( '.hubwoo-form-wizard-content' ).removeClass( 'show' );
					prev.parents( '.hubwoo-form-wizard-content' ).prev( '.hubwoo-form-wizard-content' ).addClass( 'show' );
					jQuery( document ).find( '.hubwoo-form-wizard-content' ).each(
						function() {
							if ( jQuery( this ).hasClass( 'show' ) ) {
								const formAtrr = jQuery( this ).attr( 'data-tab-content' );
								jQuery( document ).find( '.hubwoo-form-wizard-wrapper li a' ).each(
									function() {
										if ( jQuery( this ).attr( 'data-attr' ) == formAtrr ) {
											jQuery( this ).addClass( 'active' );
											const innerWidth = jQuery( this ).innerWidth();
											const position   = jQuery( this ).position();
											jQuery( document ).find( '.hubwoo-form-wizardmove-button' ).css( { left: position.left, width: innerWidth } );
										} else {
											jQuery( this ).removeClass( 'active' );
										}
									},
								);
							}
						},
					);
				},
			);

			jQuery( '.mwb-woo__accordian-heading' ).click(
				function() {
					const name = $( this ).data( 'name' );
					jQuery( '.grToCreate' ).removeClass( 'fa-minus' ).addClass( 'fa-plus' );
					if ( jQuery( '.mwb-woo__accordian-heading' ).is( '.gr_created' ) ) {
						jQuery( '.gr_created' ).parent( 'div' ).children( 'i' ).removeClass( 'fa fa-chevron-down' ).addClass( 'fa fa-chevron-right' );
					}

					if ( ! jQuery( this ).hasClass( 'active' ) ) {
						jQuery( '#fa-drag-' + name ).removeClass( 'fa-plus' ).addClass( 'fa-minus' );
						if ( jQuery( this ).hasClass( 'gr_created' ) ) {
							jQuery( this ).parent( 'div' ).children( 'i' ).removeClass( 'fa fa-chevron-right' ).addClass( 'fa fa-chevron-down' );
							jQuery( '#' + 'fa-' + name ).removeClass( 'fa fa-chevron-right' ).addClass( 'fa fa-chevron-down' );
						}
						if ( ! jQuery( this ).hasClass( 'gr_uncreated' ) ) {
							jQuery( '.mwb-woo__accordian-heading' ).removeClass( 'active' );
							jQuery( '.mwb-woo__accordion-content' ).slideUp( 500 );
							jQuery( this ).addClass( 'active' );
							jQuery( this ).parents( '.mwb-woo__accordion-wrapper' ).children( '.mwb-woo__accordion-content' ).slideDown( 500 );
						}
					} else if ( jQuery( this ).hasClass( 'active' ) ) {
						if ( jQuery( this ).hasClass( 'gr_created' ) ) {
							jQuery( '#' + 'fa-' + name ).removeClass( 'fa fa-chevron-down' ).addClass( 'fa fa-chevron-right' );
						}
						if ( ! jQuery( this ).hasClass( 'gr_uncreated' ) ) {
							jQuery( this ).removeClass( 'active' );
							jQuery( this ).parents( '.mwb-woo__accordion-wrapper' ).children( '.mwb-woo__accordion-content' ).slideUp( 500 );
						}
					}
				},
			);

			jQuery( '.mwb-woo__accordian-heading' ).hover(
				function() {
					if ( jQuery( this ).parent( 'div' ).children( 'span' ).hasClass( 'grCreateNew' ) ) {
						jQuery( this ).prop( 'title', 'Create group first, before creating properties.' );
					}
				},
			);

			jQuery( 'input.hub-group' ).on(
				'click',
				function() {
					const groupID        = jQuery( this ).data( 'group' );
					const groupRequired  = jQuery( this ).data( 'req' );
					const currentChecked = jQuery( this ).prop( 'checked' );

					if ( 'yes' == groupRequired ) {
						return false;
					}

					jQuery( 'div#' + groupID ).find( 'input[type=checkbox]' ).each(
						function() {
							jQuery( this ).attr( 'checked', currentChecked );
							if ( currentChecked ) {
								jQuery( this ).removeAttr( 'disabled' );
							} else {
								jQuery( this ).attr( 'disabled', true );
							}
						},
					);
				},
			);

			jQuery( '#hubwoo-re-auth' ).click(
				( e ) => {
                e.preventDefault();
                saveUpdates( [ 'hubwoo_pro_oauth_success', 'hubwoo_pro_account_scopes', 'hubwoo_pro_valid_client_ids_stored' ], 'delete' );
                window.location.href = jQuery( '#hubwoo-re-auth' ).attr( 'href' );
				},
			);

			jQuery( 'input.hub-prop' ).on(
				'click',
				function() {
                const propRequired = jQuery( this ).data( 'req' );
	                if ( 'yes' == propRequired ) {
	                    return false;
	                }
				},
			);

			jQuery( '#hub-lists-form' ).submit(
				async( event ) => {
                event.preventDefault();
                jQuery( '.hubwoo-list__manage' ).slideUp();
                jQuery( '.hubwoo-btn-list' ).hide();
                jQuery( '.hubwoo-list__progress' ).css( 'display', 'block' );
                updateProgressBar( 0 );
                const countLists     = jQuery( 'input[name=\'selectedLists[]\']' ).length;
                const currentCounter = 0;
                const maxCounter     = countLists != null ? countLists : 3;
                const data           = jQuery( 'form#hub-lists-form' ).serializeArray();
                const selectedLists  = [];
                jQuery.each(
						data,
                        function( i, input ) {
                            if ( input.name == 'selectedLists[]' ) {
                                selectedLists.push( input.value );
                            }
                        },
					);
				let oauth_response = await jQuery.post( ajaxUrl, { action: 'hubwoo_check_oauth_access_token', hubwooSecurity } );
				oauth_response     = jQuery.parseJSON( oauth_response );
				const oauth_status = oauth_response.status;

				if ( oauth_status ) {
					let listsResponse = await jQuery.post( ajaxUrl, { action: 'hubwoo_get_lists', hubwooSecurity } );
					listsResponse     = jQuery.parseJSON( listsResponse );

					const tempLists = [];

					jQuery.each(
						listsResponse,
						function( i, singleList ) {
							if ( selectedLists.includes( singleList.name ) ) {
								tempLists.push( singleList );
							}
						},
					);

					dataCounter.totalCount = tempLists.length;
					try {
						const createdLists   = [];
						let upgradeRequired  = false;
						const allListResults = await Promise.all(
							tempLists.map(
								async( singleList ) => {
                                try {
                                    const response = await createLists( singleList );
                                    if ( response.status_code == 200 || response.status_code == 409 ) {
                                        createdLists.push( singleList.name );
                                    }
                                    return response;
                                } catch ( errors ) {
										if ( errors.status_code == 402 ) {
											upgradeRequired = true;
										} else {
                               				 console.error( errors );
										}
									}
								},
							),
						);

						if ( upgradeRequired ) {
							jQuery( '.hubwoo_pop_up_wrap' ).slideDown( 'slow' );
						} else {
							await saveUpdates( { 'hubwoo-lists-created': createdLists, 'hubwoo_pro_lists_setup_completed': 1 } );
							transferScreen( 'move-to-sync' );
						}
					} catch ( error ) {
						console.error( error );
					}
				} else {
	                alert( hubwooWentWrong );
	                return false;
				}
				},
			);

			jQuery( 'span.hubwoo-create-single-group' ).on(
				'click',
				function() {
					const name = $( this ).data( 'name' );
					const job  = $( this );

					manageSpinText( job, 'add' );

					jQuery.post(
						ajaxUrl,
						{ action: 'hubwoo_check_oauth_access_token', hubwooSecurity },
						function( response ) {
							const oauth_response = jQuery.parseJSON( response );
							const oauth_status   = oauth_response.status;
							if ( oauth_status ) {
								jQuery.post(
									ajaxUrl,
									{ action: 'hubwoo_create_single_group', name, hubwooSecurity },
									function( response ) {
										const proresponse   = jQuery.parseJSON( response );
										const proerrors     = proresponse.errors;
										const prohubMessage = '';

										if ( ! proerrors ) {
											const proresponseCode = proresponse.status_code;

											if ( proresponseCode == 200 ) {
												manageSpinText( job, 'remove' );
												job.addClass( 'grSuccess' );

												jQuery( '#' + name ).removeClass( 'mwb-woo__accordion-content-disable' ).addClass( 'mwb-woo__accordion-content' );
												if ( jQuery( '.mwb-woo__accordian-heading' ).hasClass( name ) ) {
													jQuery( '.' + name ).removeClass( 'gr_uncreated' ).addClass( 'gr_created' );
												}
											} else if ( proresponseCode == 409 ) {
												manageSpinText( job, 'remove' );
												job.addClass( 'grSuccess' );
												jQuery( '#' + name ).removeClass( 'mwb-woo__accordion-content-disable' ).addClass( 'mwb-woo__accordion-content' );
												if ( jQuery( '.mwb-woo__accordian-heading' ).hasClass( name ) ) {
													jQuery( '.' + name ).removeClass( 'gr_uncreated' ).addClass( 'gr_created' );
												}
											} else {
												manageSpinText( job, 'remove', 'failed' );
											}
										}
									},
								);
							} else {
								manageSpinText( job, 'remove', 'failed' );
								return false;
							}
						},
					);
				},
			);

			jQuery( 'span.hubwoo-create-single-field' ).on(
				'click',
				function() {
					const name = $( this ).data( 'name' );

					const group = $( this ).data( 'group' );

					const job = $( '.pr-' + name );
					job.removeClass( 'fa-plus' );
					job.addClass( 'fa-spinner' );
					job.addClass( 'fa-spin' );
					jQuery.post(
						ajaxUrl,
						{ action: 'hubwoo_check_oauth_access_token', hubwooSecurity },
						function( response ) {
							const oauth_response = jQuery.parseJSON( response );
							const oauth_status   = oauth_response.status;

							if ( oauth_status ) {
								jQuery.post(
									ajaxUrl,
									{ action: 'hubwoo_create_single_property', name, group, hubwooSecurity },
									function( response ) {
										const proresponse   = jQuery.parseJSON( response );
										const proerrors     = proresponse.errors;
										const prohubMessage = '';

										if ( ! proerrors ) {
											const proresponseCode = proresponse.status_code;

											if ( proresponseCode == 200 ) {
												job.removeClass( 'fa-spinner' );
												job.removeClass( 'fa-spin' );
												job.addClass( 'fa-check' );
											} else if ( proresponseCode == 409 ) {
												job.removeClass( 'fa-spinner' );
												job.removeClass( 'fa-spin' );
												job.addClass( 'fa-check' );
											} else {
												job.removeClass( 'fa-spinner' );
												job.removeClass( 'fa-spin' );
												job.addClass( 'fa-plus' );
											}
										}
									},
								);
							} else {
								job.removeClass( 'fa-spinner' );
								job.removeClass( 'fa-spin' );
								job.addClass( 'fa-plus' );
								return false;
							}
						},
					);
				},
			);

			jQuery( '.hubwoo-create-single-list' ).on(
				'click',
				function() {
					const name = $( this ).data( 'name' );
					const job  = $( this );

					manageSpinText( job, 'add' );

					jQuery.post(
						ajaxUrl,
						{ action: 'hubwoo_check_oauth_access_token', hubwooSecurity },
						function( response ) {
							const oauth_response = jQuery.parseJSON( response );
							const oauth_status   = oauth_response.status;

							if ( oauth_status ) {
								jQuery.post(
									ajaxUrl,
									{ action: 'hubwoo_create_single_list', name, hubwooSecurity },
									function( response ) {
										const proresponse   = jQuery.parseJSON( response );
										const proerrors     = proresponse.errors;
										const prohubMessage = '';

										if ( ! proerrors ) {
											const proresponseCode = proresponse.status_code;
											if ( proresponseCode == 200 ) {
												manageSpinText( job, 'remove' );
											} else if ( proresponseCode == 409 ) {
												manageSpinText( job, 'remove' );
											} else if ( proresponseCode == 400 ) {
												jQuery( '.hubwoo_pop_up_wrap' ).slideDown( 'slow' );
												manageSpinText( job, 'remove', 'failed' );
											} else {
												manageSpinText( job, 'remove', 'failed' );
											}
										}
									},
								);
							} else {
								manageSpinText( job, 'remove', 'failed' );
								return false;
							}
						},
					);
				},
			);

			jQuery( '#save-deal-stages' ).click(
				async( e ) => {
                e.preventDefault();
                if ( jQuery( '#hubwoo-ecomm-deal-stage' ).length ) {
                    await saveUpdates( { 'hubwoo_ecomm_mapping_setup': 'yes' } );
                }
                window.location.reload( true );
				},
			);

			setInterval(
				function() {
					jQuery( '.hubwoo-progress-bar' ).map(
						async function( element ) {
							const currentElement = jQuery( this );
							if ( 'yes' == currentElement.data( 'sync-status' ) ) {
								  const type   = currentElement.data( 'sync-type' );
								  let response = await jQuery.ajax( { url: ajaxUrl, type: 'POST', data: { action: 'hubwoo_sync_status_tracker', hubwooSecurity, process: type } } );
								  response     = jQuery.parseJSON( response );
								  currentElement.css( 'width', response.percentage + '%' );
								  currentElement.text( response.percentage + '%' );
								if ( response.hasOwnProperty( 'eta' ) ) {
									const currentDesc = jQuery( '.sync-desc[data-sync-type=\'' + type + '\']' );
									let text          = currentDesc.text().trim();
									const previousEta = currentDesc.attr( 'data-sync-eta' );
									text              = text.replace( previousEta, response.eta );
									currentDesc.attr( 'data-sync-eta', response.eta );
									currentDesc.text( text );
								}
								if ( 'no' == response.is_running || 100 == currentElement.data( 'percentage' ) || 100 == response.percentage ) {
									currentElement.css( 'width', '100%' );
									currentElement.text( '100%' );									
									window.location.href = window.location.href;
								}
							}
						}
					);
				},
				25000
			);

			jQuery( '#reset-deal-stages' ).click(
				async( e ) => {
                e.preventDefault();
                await jQuery.ajax( { url: ajaxUrl, type: 'POST', data: { action: 'hubwoo_ecomm_setup', hubwooSecurity, process: 'reset-mapping' } } );
                window.location.reload( true );
				},
			);

			jQuery( '.hubwoo-create-single-workflow-data' ).on(
				'click',
				function() {
					const btnThis = jQuery( this );
					var name      = jQuery( this ).data( 'name' );
					const tab     = jQuery( this ).closest( '.hubwoo-field-text-col' );
					tab.removeClass( 'hubwoo-align-class' ).addClass( 'align-big' );
					var name  = $( this ).data( 'name' );
					const job = $( this );

					manageSpinText( job, 'add' );

					jQuery.post(
						ajaxUrl,
						{ action: 'hubwoo_check_oauth_access_token', hubwooSecurity },
						function( response ) {
							const oauth_response = jQuery.parseJSON( response );
							const oauth_status   = oauth_response.status;

							if ( oauth_status ) {
								jQuery.post(
									ajaxUrl,
									{ action: 'hubwoo_create_single_workflow', name, hubwooSecurity },
									function( response ) {
										const proresponse   = jQuery.parseJSON( response );
										const proerrors     = proresponse.errors;
										const prohubMessage = '';

										if ( ! proerrors ) {
											const proresponseCode = proresponse.status_code;

											if ( proresponseCode == 200 ) {
												manageSpinText( job, 'remove' );

												tab.removeClass( 'align-big' ).addClass( 'hubwoo-align-class' );

												jQuery.post(
													ajaxUrl,
													{ action: 'hubwoo_update_workflow_tab' },
													function( data ) {
														var data = JSON.parse( data );
														$.each(
															data,
															function( index, value ) {
																$( '.workflow-tab[data-name="' + value + '"]' ).removeClass( 'hubwoo-disabled' );
															},
														);
													},
												);

												btnThis.parents( '.hubwoo-field-text-col' ).children( 'div.hubwoo-field-checked' ).show();
											} else if ( proresponseCode == 404 ) {
												let message = JSON.parse( proresponse.response );
												message     = message.message;
												manageSpinText( job, 'remove', 'failed' );
												tab.removeClass( 'align-big' ).addClass( 'hubwoo-align-class' );
												alert( message );
											} else {
												alert( hubwooWentWrong );
												manageSpinText( job, 'remove', 'failed' );
												tab.removeClass( 'align-big' ).addClass( 'hubwoo-align-class' );
											}
										}
									},
								);
							} else {
								tab.removeClass( 'align-big' ).addClass( 'hubwoo-align-class' );
								manageSpinText( job, 'remove', 'failed' );
								return false;
							}
						},
					);
				},
			);

			jQuery( '.hubwoo-onquest' ).select2(
				{
					placeholder: "Select from the options",
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						delay: 100,
						type: 'POST',						
						data( params ) {
							return {
								q: params.term,
								action: 'hubwoo_get_onboard_form',
								key: jQuery(this).attr('name'),
								hubwooSecurity:hubwooSecurity
							};
						},
						processResults( data ) {
							const options = [];
							if ( data ) {
								$.each(
									data,
									function( index, text ) {
										options.push( { id: text[ 0 ], text: text[ 1 ] } );
									},
								);
							}
							return {
								results: options,
							};
						},
						cache: true,
					},
				},
			);

			jQuery( '#hubwoo-order-statuses , #hubwoo_ecomm_order_ocs_status' ).select2(
				{
					placeholder: 'Processing, Completed etc.',
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						delay: 200,
						data( params ) {
							return {
								q: params.term,
								action: 'hubwoo_search_for_order_status',
							};
						},
						processResults( data ) {
							const options = [];
							if ( data ) {
								$.each(
									data,
									function( index, text ) {
										options.push( { id: text[ 0 ], text: text[ 1 ] } );
									},
								);
							}
							return {
								results: options,
							};
						},
						cache: true,
					},
				},
			);

			jQuery( '#hubwoo-selected-user-roles, #hubwoo_customers_role_settings' ).select2(
				{
					placeholder: 'All Users Roles are Selected except Guest.',
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						delay: 200,
						data( params ) {
							return {
								q: params.term,
								action: 'hubwoo_get_for_user_roles',
							};
						},
						processResults( data ) {
							const options = [];
							if ( data ) {
								$.each(
									data,
									function( index, text ) {
										options.push( { id: text[ 0 ], text: text[ 1 ] } );
									},
								);
							}
							return {
								results: options,
							};
						},
						cache: true,
					},
				},
			);

			jQuery( 'input[type="checkbox"]' ).on(
				'change',
				function() {
					const elementId  = jQuery( this ).attr( 'id' );
					const checkboxes = trackInputCheckboxes();
					if ( checkboxes.hasOwnProperty( elementId ) ) {
						const hideField = checkboxes[ elementId ];
						if ( jQuery( this ).is( ':checked' ) ) {
							jQuery( hideField ).closest( 'tr' ).fadeIn();
						} else {
							jQuery( hideField ).closest( 'tr' ).fadeOut();
						}
					}
				},
			);

			jQuery( '.date-picker' ).datepicker( { dateFormat: 'dd-mm-yy', maxDate: 0, changeMonth: true, changeYear: true } );

			jQuery( document ).on(
				'click',
				'#hubwoo-osc-instant-sync',
				async function( event ) {
					event.preventDefault();
					jQuery( '#hubwoo-osc-instant-sync' ).hide();
					const progress = 0;
					jQuery( '#hubwoo-ocs-form' ).slideUp( 600 );
					jQuery( '#hubwoo-osc-instant-sync' ).addClass( 'hubwoo-disable' );
					jQuery( '#hubwoo-osc-schedule-sync' ).addClass( 'hubwoo-disable' );
					jQuery( '.hubwoo-progress-wrap' ).css( 'display', 'block' );
					const totalUsers = jQuery( '.hubwoo-osc-instant-sync' ).data( 'total_users' );
					await saveUpdates( { 'hubwoo_total_ocs_need_sync': totalUsers } );
					await saveUpdates( [ 'hubwoo_ocs_contacts_synced' ], 'delete' );
					updateProgressBar( 0 );
					startContactSync( 1, progress );
				},
			);

			jQuery( '.hubwoo-date-picker' ).datepicker( { dateFormat: 'dd-mm-yy', maxDate: 0, changeMonth: true, changeYear: true } );

			jQuery( '#hubwoo-pro-email-logs' ).on(
				'click',
				function( e ) {
					e.preventDefault();
					jQuery( '#hubwoo_email_loader' ).css( { display: 'inline-block' } );

					jQuery.post(
						ajaxUrl,
						{ action: 'hubwoo_email_the_error_log', hubwooSecurity },
						function( response ) {
							const res_parsed = jQuery.parseJSON(response);
							if ( res_parsed != null ) {
								if ( res_parsed == 'success' ) {
									alert( hubwooMailSuccess );
									jQuery( '#hubwoo_email_loader' ).css( { display: 'none' } );
									jQuery( '#hubwoo_email_success' ).css( { display: 'inline-block' } );
								} else {
									alert( hubwooMailFailure );
									jQuery( '#hubwoo_email_loader' ).css( { display: 'none' } );
								}
							} else {
								// close the popup and show the error.
								alert( hubwooMailFailure );
								jQuery( '#hubwoo_email_loader' ).css( { display: 'none' } );
							}
							jQuery( '#hubwoo_email_success' ).css( { display: 'none' } );
						},
					);
				},
			);

			jQuery( '.hubwoo-general-settings-fields' ).on(
				'change',
				function() {
					const data             = jQuery( '#plugin-settings-gen-adv' ).serializeArray();
					const enableKeys       = [
						{ key: 'hubwoo_checkout_optin_enable', status: 'no' },
						{ key: 'hubwoo_registeration_optin_enable', status: 'no' },
						{ key: 'hubwoo_subs_settings_enable', status: 'no' },						
					];
					const multiSelectKeys  = [
						{ key: 'hubwoo-order-statuses', status: 'EMPTY_ARRAY' },
						{ key: 'hubwoo-selected-user-roles', status: 'EMPTY_ARRAY' },
					];
					const preparedFormData = prepareFormData( data, enableKeys, true );
					multiSelectKeys.map(
						( singleKey ) => {
                        if ( preparedFormData.hasOwnProperty( singleKey.key ) == false ) {
                            preparedFormData[ singleKey.key ] = singleKey.status;
                        }
						},
					);
					saveUpdates( preparedFormData );
				},
			);

			jQuery( '.hubwoo_rfm_data_fields' ).on(
				'change',
				debounce(
					function() {
						const data             = jQuery( '#hubwoo-rfm-form' ).serializeArray();
						const preparedFormData = prepareFormData( data );
						saveUpdates( preparedFormData );
					},
					800,
				),
			);

			// Deals

			if( new URLSearchParams(window.location.search).get('hubwoo_tab') == 'hubwoo-deals') {
				jQuery.post(
					ajaxUrl,
					{ action: 'hubwoo_get_current_sync_status', hubwooSecurity, data: { type: 'deal' } },
					function( response ) {
						// response = jQuery.parseJSON( response );
						if ( response != true || response != 1 ) {
							getDealsUsersToSync();
						} else {
							$("#hubwoo_deals_ocs_form :input").map(function() {
								$(this).attr( { disabled: 'true' } );
							})	
						}
					},
				);
			}

			jQuery( '.hubwoo_real_time_changes' ).on(
				'change',
				debounce(
					() => {
	                    const data            = jQuery( '#hubwoo_real_time_deal_settings' ).serializeArray();
	                    const enableKeys      = [ { key: 'hubwoo_ecomm_deal_enable', status: 'no' }, { key: 'hubwoo_assoc_deal_cmpy_enable', status: 'no' } ];
	                    const multiSelectKeys = [ { key: 'hubwoo_ecomm_won_stages', status: 'EMPTY_ARRAY' } ];
	                    const formData        = prepareFormData( data, enableKeys, true );
	                    multiSelectKeys.map(
								( singleKey ) => {
	                            if ( formData.hasOwnProperty( singleKey.key ) == false ) {
	                                formData[ singleKey.key ] = singleKey.status;
	                            }
								},
							);
						saveUpdates( formData );
					},
					800,
				),
			);

			jQuery( '.hubwoo_ecomm_mapping' ).on(
				'change',
				debounce(
					() => {
	                    const data     = jQuery( '.hubwoo_save_ecomm_mapping' ).serializeArray();
	                    const formData = [];
	                    for ( let index = 0; index < data.length; index++ ) {
	                        const preparedData      = {};
	                        preparedData.status     = data[ index ].value;
	                        preparedData.deal_stage = data[ ++index ].value;
	                        formData.push( preparedData );
	                    }
	                    saveUpdates( { 'hubwoo_ecomm_final_mapping': formData } );
					},
					300,
				),
			);

			jQuery( '#hubwoo_ecomm_won_stages' ).select2(
				{
					placeholder: 'Select Winning Deal Stages.',
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						delay: 200,
						data( params ) {
							return {
								q: params.term,
								action: 'hubwoo_deals_search_for_stages',
							};
						},
						processResults( data ) {
							const options = [];
							if ( data ) {
								$.each(
									data,
									function( index, text ) {
										options.push( { id: text[ 0 ], text: text[ 1 ] } );
									},
								);
							}
							return {
								results: options,
							};
						},
						cache: true,
					},
				},
			);

			jQuery( '.hubwoo-ecomm-settings-select' ).on(
				'change',
				async() => {
	                const data        = jQuery( '#hubwoo_deals_ocs_form' ).serializeArray();
	                const enableKeys  = [ { key: 'hubwoo_ecomm_order_date_allow', status: 'no' } ];
	                const multiSelectKeys  = [ { key: 'hubwoo_ecomm_order_ocs_status', status: 'EMPTY_ARRAY' } ];
	                const formData    = prepareFormData( data, enableKeys, true );
	                multiSelectKeys.map(
						( singleKey ) => {
	                        if ( formData.hasOwnProperty( singleKey.key ) == false ) {
	                            formData[ singleKey.key ] = singleKey.status;
	                        }
						}
					);

					if(formData.hubwoo_ecomm_order_date_allow == 'no') {
						delete formData.hubwoo_ecomm_order_ocs_from_date
						delete formData.hubwoo_ecomm_order_ocs_upto_date
					}         
	                const syncReponse = await jQuery.post( ajaxUrl, { action: 'hubwoo_get_current_sync_status', hubwooSecurity, data: { type: 'deal' } } );
					if ( syncReponse != true || syncReponse != 1 ) {
						const savedResponse = await saveUpdates( formData );
						if ( savedResponse ) {
							const ocsCount = await jQuery.get( ajaxUrl, { action: 'hubwoo_ecomm_get_ocs_count', hubwooSecurity } );
							if ( ocsCount !== undefined || ocsCount !== null ) {
								jQuery( '.hubwoo_deals_message[data-sync-type="order"]' ).slideDown( 20 );
								jQuery( '.hubwoo_deals_message[data-sync-type="order"]' ).css( 'display', 'inline-block' );
								jQuery( '.hubwoo-group-wrap__deal_notice[data-type="pBar"]' ).slideDown( 200 );

								let message = '';

								if ( ocsCount > 0 ) {
									message = `We have found ${ ocsCount } Orders for the selected order statuses`;
									if(jQuery('#hubwoo_ecomm_order_date_allow').is(':checked')) {
										message += ' and date range'
									}																		
									jQuery( '.manage_deals_ocs' ).fadeIn();
								} else {
									jQuery( '.manage_deals_ocs' ).fadeOut();
									message = 'No Orders found for the selected order statuses';
									if(jQuery('#hubwoo_ecomm_order_date_allow').is(':checked')) {
										message += ' and date range'
									}
								}

								jQuery( '.hubwoo_deals_message[data-sync-type="order"]' ).text( message );
							}
						}
					} else {
						jQuery( '.hubwoo-deals-settings-select' ).attr( { disabled: 'true' } );
                	}
				},
			);
			
			$('#hubwoo-initiate-oauth').click( function(e) {
				e.preventDefault()
				const url = $(this).attr('href')
				// track connect count and redirect
				window.location.href = url
			})

			jQuery( '.manage_deals_ocs, .manage_contact_sync, .manage_product_sync' ).click(
				async function() {
					const syncAction = jQuery( this ).data( 'action' );
					if ( syncAction !== undefined ) {
						if( 'run-ecomm-setup' === syncAction ) {
							await runEcommSetup();
						} else {
							await jQuery.post( ajaxUrl, { action: 'hubwoo_manage_sync', hubwooSecurity, process: syncAction } );
						}
						window.location.reload( true );
					}
				},
			);

			// Abandoned Cart
			jQuery( '.hubwoo-abncart-setup-form' ).on(
				'change',
				debounce(
					function() {
						const data             = jQuery( '.hubwoo-abncart-setup-form' ).serializeArray();
						const enableKeys       = [
							{ key: 'hubwoo_abncart_enable_addon', status: 'no' },
							{ key: 'hubwoo_abncart_guest_cart', status: 'no' },
							{ key: 'hubwoo_abncart_delete_old_data', status: 'no' },
						];
						const preparedFormData = prepareFormData( data, enableKeys, true );
						saveUpdates( preparedFormData );
					},
					700,
				),
			);

			// Show Setup Content on Click
			jQuery( '.hubwoo-btn-cshow__btn a' ).click(
				function( e ) {
					e.preventDefault();
					$( this ).parents( '.hubwoo-box-card' ).find( '.hubwoo-btn-cshow__content' ).toggle( 'slow' );
				},
			);

			jQuery( '.hubwoo-btn-data' ).click(
				function( e ) {
					e.preventDefault();
					jQuery( this ).parents( '.hubwoo-btn-list' ).next( '.hubwoo-sub-content' ).children( 'div' ).hide();
					const datavalue = jQuery( this ).data( 'action' );
					if ( datavalue == 'group_setup' ) {
						jQuery( this ).parents( '.hubwoo-btn-list' ).next( '.hubwoo-sub-content' ).children( '.hubwoo-group__progress' ).show();
						jQuery( '#hubwoo_create_group_prop_setup' ).hide();
						jQuery( '.gen-text' ).hide();
						jQuery( '#hub-gr-props-form' ).submit();
					} else if ( datavalue == 'group_manage_setup' ) {
						jQuery( '.grp-pr-heading' ).text( 'Created Properties & Groups' );
						jQuery( '.hubwoo-content__para' ).hide();
						jQuery( this ).parents( '.hubwoo-btn-list' ).next( '.hubwoo-sub-content' ).children( '.hubwoo-group__manage' ).slideDown( 'slow' );
						jQuery( '.hubwoo-group-desc' ).show();
						jQuery( '#hubwoo_create_group_prop_setup' ).hide();
						jQuery( '#hubwoo-manage-setup' ).hide();
					}

					if ( datavalue == 'lists_setup' ) {
						jQuery( this ).parents( '.hubwoo-btn-list' ).next( '.hubwoo-sub-content' ).children( '.hubwoo-list__progress' ).show();
						jQuery( '#hub-lists-form' ).submit();
					} else if ( datavalue == 'lists_setup_manage' ) {
						jQuery( this ).parents( '.hubwoo-btn-list' ).next( '.hubwoo-sub-content' ).children( '.hubwoo-list__manage' ).slideDown( 'slow' );
						jQuery( '.list-setup-heading' ).text( 'Lists' );
						jQuery( '.hubwoo-content__para' ).hide();
						jQuery( '.hubwoo-list-desc' ).show();
						jQuery( '.hubwoo-btn-data' ).hide();
						jQuery( '#hub-lists-form' ).slideDown();
					}
				},
			);

			jQuery( '.hubwoo_manage_screen' ).click(
				() => {
                const screenKey = jQuery( '.hubwoo_manage_screen' ).data( 'process' );
                const lastKey   = jQuery( '.hubwoo_manage_screen' ).data( 'tab' );
                transferScreen( screenKey, lastKey );
				},
			);

			jQuery( '.hubwoo-adv-settingg__btn a' ).click(
				function() {
					jQuery( this ).parents( '.hubwoo-adv-settingg' ).children( '.hubwoo-adv-settingg__form' ).toggle( 'slow' );
				},
			);
			jQuery( '.hubwoo-deal-wrap-con__h-btn a' ).click(
				function( e ) {
					e.preventDefault();
					jQuery( this ).parents( '.hubwoo-deal-wrap-con' ).children( '.hubwoo-deal-wrap-con__store' ).toggle( 'slow' );
				},
			);

			jQuery( '.hubwoo-manage-account' ).on('click',
				function() {
					let formData = {}
					switch (jQuery(this).data('type')) {
						case 'disconnect-form':
							jQuery('.hubwoo_pop_up_wrap').fadeIn();
							break;	
						case 'disconnect':
							formData = jQuery('.hubwoo-disconnect-form').serializeArray();
			                const enableKeys = [ { key: 'delete_meta', status: 'no' } ];
			                formData = prepareFormData( formData, enableKeys );			
						case 'change-account':
							jQuery('.hubwoo-discon-spinner').slideDown('slow')
							const currentPage  = window.location.href;
							let redirectUrl  = currentPage.substring( 0, currentPage.indexOf( 'hubwoo' ) );
							redirectUrl += 'hubwoo';

							const action = { action: 'hubwoo_disconnect_account', hubwooSecurity }

			                if(Object.keys(formData).length !== 0) {
			                	action.data = formData
			                }
							jQuery.post(
								ajaxUrl,
								action,
								function( status ) {
									if ( status ) {
										window.location.href = redirectUrl
									}
								},
							);	
							break;
						case 'cancel':
							jQuery('.hubwoo_pop_up_wrap').hide();							
						default:
							break;						
					}
				}
			);

			jQuery( '.hubwoo-ocs-input-change' ).on(
				'change',
				async() => {
                const data             = jQuery( '#hubwoo-ocs-form' ).serializeArray();
                const enableKeys       = [ { key: 'hubwoo_customers_manual_sync', status: 'no' } ];
                const multiSelectKeys  = [ { key: 'hubwoo_customers_role_settings', status: 'EMPTY_ARRAY' } ];
                const preparedFormData = prepareFormData( data, enableKeys, true );
                multiSelectKeys.map(
						( singleKey ) => {
                        if ( preparedFormData.hasOwnProperty( singleKey.key ) == false ) {
                            preparedFormData[ singleKey.key ] = singleKey.status;
                        }
						},
					);
				saveUpdates( preparedFormData );
				jQuery.post(
					ajaxUrl,
					{ action: 'hubwoo_get_current_sync_status', hubwooSecurity },
					function( response ) {
						// response = jQuery.parseJSON( response );
						if ( response != true || response != 1 ) {
							getCurrentUsersToSync();
						} else {
							jQuery( '#hubwoo_customers_role_settings' ).attr( { disabled: 'true' } );
							jQuery( '#hubwoo_customers_manual_sync' ).attr( { disabled: 'true' } );
						}
					},
				);
				},
			);

			jQuery(document).on('change', '.which_hubspot_packages_do_you_currently_use_', function() {
				var hubwoo_package = jQuery(this).val();					
				if( hubwoo_package.includes("I don’t currently use HubSpot") ){
					jQuery('.hubwoo_register').removeClass('hidefield');
				} else {
					jQuery('.hubwoo_register').addClass('hidefield');
				}

			});

			jQuery('.hubwoo-onboard-manage').click( async function(e){
				e.preventDefault()

				switch (jQuery(this).data('type')) {
					case 'sync':

						let inputFields = true
						let formData = jQuery('#hubwoo-onboarding-form').serializeArray();
						formData = prepareFormData(formData, null, true);
						let hubwoo_package = jQuery('.which_hubspot_packages_do_you_currently_use_').val();
						
						const onboardKeys  = [
							{ key: 'mwb_hs_familarity', status: '' },
							{ key: 'mwb_woo_familarity', status: '' },
							{ key: 'which_hubspot_packages_do_you_currently_use_', status: '' },
							{ key: 'firstname', status: '' },
							{ key: 'lastname', status: '' },
							{ key: 'company', status: '' },
							{ key: 'website', status: '' },
							{ key: 'email', status: '' },
							{ key: 'phone', status: '' },							
						];

						if( ! hubwoo_package.includes("I don’t currently use HubSpot") ){
							onboardKeys.splice(3,4);
							delete formData["firstname"];
							delete formData["lastname"];
							delete formData["company"];
							delete formData["website"];
						}

						onboardKeys.map(
							( singleKey ) => {
		                        if ( formData.hasOwnProperty( singleKey.key ) == false ) {
		                            formData[ singleKey.key ] = singleKey.status;
		                        }
							},
						);	
						Object.keys(formData).forEach((key) => {
							if(formData[key] !== '') {
								jQuery('.hubwoo-onboard-img[name='+key+']').show()
							} else {
								inputFields = false
								jQuery('.hubwoo-onboard-img[name='+key+']').hide()
							}
						})

						if(inputFields) {
							jQuery('.hubwoo-onboard-notice').slideUp()
							jQuery('.onboard-spinner').slideDown()
							await jQuery.post( ajaxUrl, { action: 'hubwoo_onboard_form', formData, hubwooSecurity } );
						} else {
						   	$('html,body').animate({ scrollTop: $(".mwb-heb-wlcm__title").outerHeight() + 220 }, 'slow', function() {
								jQuery('.hubwoo-onboard-notice').slideDown('slow')
						   	});
							break;
						}
					case 'skip':
						await saveUpdates({'hubwoo_onboard_user': 'yes'})
						jQuery("#hubwoo-onboard-user").hide()
						jQuery("#hubwoo-visit-dashboard").show()
					default:
						break;						
				}
			})

			jQuery('.hubwoo-action-icon').tipTip({ 
				'attribute': 'data-tip',
				delay: 150,
				fadeIn: 150,
				fadeOut: 200,				
			})

			$('.hubwoo-action-icon').click( async function(e) {
				if('no' == $(this).data("status")) {
					e.preventDefault()

					if( 'yes' ==  $(this).attr('data-sync-status')) {
						return;
					}

					let triggerStatus = false
					const type = $(this).data('type');
				    const firstChild = document.body.firstChild;
					const popupWrap = document.createElement("div");
				    popupWrap.className = 'hubwoo_pop_up_wrap';
				    firstChild.parentNode.insertBefore(popupWrap, firstChild);	
				    $(popupWrap).css('display','none');
				    $(popupWrap).fadeIn()
				    $(popupWrap).append('<div class="pop_up_sub_wrap"><div class="hubwoo-disconnect-wrapper"><h2></h2><p></p></div></div>');
					$('.hubwoo-disconnect-wrapper').append('<div class="hubwoo-discon-spinner"><span class="fa fa-spin fa-spinner"></span></div>');
					$('.hubwoo-disconnect-wrapper').append('<div class="hubwoo-discon-btn"><a href="javascript:void(0);" data-type="sync" class="hubwoo-btn--primary hubwoo-objects">Start sync</a><a href="javascript:void(0);" data-type="cancel" class="hubwoo-btn--primary hubwoo-btn--disconnect hubwoo-btn--secondary hubwoo-objects">Not now</a></div>');
					$('.hubwoo-disconnect-wrapper > h2').text("Schedule a background sync")
					$('.hubwoo-disconnect-wrapper > p').text("Would you like to schedule a background sync for all of the "+ capitalize(type)+ "s")
					$('.hubwoo-objects').click( async function() {
						if( 'sync' == $(this).data('type') ) {
							$('.hubwoo-discon-spinner').fadeIn('slow');
							switch (type) {
								case 'contact':
									await jQuery.post( ajaxUrl, { action: 'hubwoo_manage_vids', hubwooSecurity, process: type } );
									window.location.reload()
									break;
								case 'deal':
									await jQuery.post( ajaxUrl, { action: 'hubwoo_manage_vids', hubwooSecurity, process: type } );
									window.location.reload()
									break;								
								default:
									break;
							}
						} else {
							$('.hubwoo_pop_up_wrap').hide();
						}
					})
				}
			})

			jQuery( '#hub-gr-props-form' ).submit(
				async( event ) => {
                event.preventDefault();
                const data           = jQuery( 'form#hub-gr-props-form' ).serializeArray();
				const progress       = 0;
				const selectedGroups = [];
				const selectedProps  = [];
                jQuery( '.hubwoo-group__progress' ).css( 'display', 'block' );
                updateProgressBar( 0 );
                jQuery.each(
					data,
                        function( i, input ) {
                            if ( input.name == 'selectedGroups[]' ) {
                                selectedGroups.push( input.value );
                            } else if ( input.name == 'selectedProps[]' ) {
                                selectedProps.push( input.value );
                            }
                        },
					);
                dataCounter.setupRunning = true;
				dataCounter.totalCount  = selectedGroups.length + selectedProps.length;
				const ecommSetupCount   = 10 * dataCounter.totalCount / 100;
				dataCounter.totalCount += ecommSetupCount;
				let oauth_response = await jQuery.post( ajaxUrl, { action: 'hubwoo_check_oauth_access_token', hubwooSecurity } );
				oauth_response     = jQuery.parseJSON( oauth_response );
				const oauth_status = oauth_response.status;

				if ( oauth_status ) {
						const allCreatedGroups     = [];
						let allCreatedProperties = [];
						let chunkedProperties = [];
						
						for (let i = selectedGroups.length - 1; i >= 0; i--) {
							const singleCreatedGroup = await createGroupsToHS( selectedGroups[i], 'contact' );
							let groupName            = null;

							if ( singleCreatedGroup.status_code == 200 ) {
								groupName = singleCreatedGroup.name;
							} else if ( singleCreatedGroup.status_code == 409 ) {
								groupName = singleCreatedGroup.name;
							}		
							allCreatedGroups.push( groupName );
						}

						for (let i = allCreatedGroups.length - 1; i >= 0; i--) {
							let groupName = allCreatedGroups[i];
							let tempPropertiesToCreate = await jQuery.ajax( { url: ajaxUrl, type: 'POST', data: { action: 'hubwoo_get_group_properties', groupName, hubwooSecurity } } );
						
							if ( tempPropertiesToCreate != undefined || tempPropertiesToCreate.length > 0 ) {
								tempPropertiesToCreate = jQuery.parseJSON( tempPropertiesToCreate );
								tempPropertiesToCreate = tempPropertiesToCreate.map( ( property ) => ( { ...property, groupName } ) );
								chunkedProperties.push(arrayChunk( tempPropertiesToCreate, 16 ) ); 
							}
						}

						if(chunkedProperties.length > 0) {

							chunkedProperties = chunkedProperties.flat();

							for (let i = chunkedProperties.length - 1; i >= 0; i--) {
								let singleChunk = chunkedProperties[i]
	                            try {
	                                const response = await createPropertiesToHS( singleChunk, 'contact' );
	                                let results    = [];

	                                if ( response.status_code == 201 ) {
	                                    results = response.body.results;
	                                    results.map(
	                                     ( createdProperty ) => {
	                                            allCreatedProperties.push( createdProperty.name );
	                                        }
	                                        );
	                                } else if ( response.status_code == 207 ) {
	                                    results                    = response.body.errors;
	                                    results.map(
	                                     ( createdProperty ) => {
	                                            const message      = createdProperty.message;
	                                            const propertyName = message.slice( message.indexOf( 'named' ) + 5, message.indexOf( 'already' ) - 1 );
	                                            allCreatedProperties.push( propertyName );
	                                        }
	                                    );
	                                }
	                            } catch ( errors ) {
									console.error( errors );
									continue;
								}
							}
						}
						
					allCreatedProperties = allCreatedProperties.map((prop) => { return prop.replace(/["']/g, "").trim()})
					await saveUpdates( { 'hubwoo-groups-created': allCreatedGroups, 'hubwoo-properties-created': allCreatedProperties, 'hubwoo_fields_setup_completed': 1, 'hubwoo_pro_setup_completed': 1, 'hubwoo_plugin_version': '1.0.5' } );
					await runEcommSetup();
					updateProgressBar( 100 );
					transferScreen( 'move-to-list' );
				}
				},
			);

			/* CSV creation and syncing of products, contacts, deals start. */
			jQuery( document ).on(
				'click',
				'#hubwoo-osc-instant-sync-historical',
				async function( event ) {
					event.preventDefault();
					jQuery( '#hubwoo-osc-instant-sync-historical' ).hide();
					const progress = 0;
					jQuery( '#hubwoo-ocs-form' ).slideUp( 600 );
					jQuery( '#hubwoo-osc-instant-sync' ).addClass( 'hubwoo-disable' );
					jQuery( '#hubwoo-osc-schedule-sync' ).addClass( 'hubwoo-disable' );
					jQuery( '.hubwoo-progress-wrap' ).css( 'display', 'block' );
					jQuery( '#hubwoo-osc-schedule-sync' ).css( 'display', 'none' );
					updateProgressBar( 0 );
					
					checkHistoricalData( 1, progress );
					
				},
			);


			const checkHistoricalData = async( step, progress ) => {
				const response = await jQuery.ajax(
					{
						type : 'POST',
						url  : ajaxUrl,
						data : {
							action : 'hubwoo_ocs_historical_contact',
							step,
							hubwooSecurity,
						},
						dataType : 'json',
					}
				).fail(
					( response ) => {
						updateProgressBar ( response.progress, 2 );
					}
				);

				var max_item = Math.ceil( response.max_time / 100);

				if ( 0 == response.progress && response.propertyError != true && response.status == true ) {
					updateProgressBar( response.progress );
					var con_batches = Math.ceil( response.contact / max_item );
					var con_batch_count = 1;
					var con_bar_update  = parseFloat( 100 / con_batches );
					con_bar_update = parseFloat( con_bar_update.toFixed(2) );
					var con_progress_bar  = parseFloat( 0 );
					var con_deal_response = '';
					var con_get_vid = 'process_request';
					
					while ( con_batch_count <= con_batches ) {

						con_progress_bar += con_bar_update;
						con_progress_bar = parseFloat( con_progress_bar.toFixed(2) );
	
						if ( con_batch_count == con_batches ) {
							con_progress_bar = 100;
							con_get_vid = 'final_request';
						}
						
						con_deal_response = await bulkContactSync( 1, con_progress_bar, max_item, con_get_vid );
						con_batch_count++;
	
					}
					

				} else if( 100 == response.progress && response.propertyError != true && response.status == true ) {
					con_get_vid = 'final_request';
					await bulkContactSync( 1, response.progress, max_item, con_get_vid );
				} else if (  response.propertyError == true ) {
					updateProgressBar( 100, 2 );

				} else {
					con_get_vid = 'final_request';
					updateProgressBar( Math.ceil( response.progress ) );
					bulkContactSync( parseInt( response.step ), parseInt( response.progress ), max_item, con_get_vid );
				}
			};

			const bulkContactSync = async( step, progress, max_item, con_get_vid ) => {
				
				const response = await jQuery.ajax(
					{
						type : 'POST',
						url  : ajaxUrl,
						data : {
							action : 'hubwoo_historical_contact_sync',
							step,
							hubwooSecurity,
							max_item,
							con_get_vid,
						},
						dataType : 'json',
					}
				).fail(
					( response ) => {
						updateProgressBar ( response.progress, 2 );
					}
				);

				if ( 100 == progress && response.propertyError != true && response.status == true ) {
					updateProgressBar( progress );
					jQuery( '.hubwoo-progress-wrap' ).children( 'p' ).append( '<strong>Completed !</strong>' );		
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).css( 'width', 0 + '%' );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).html( 0 + '%' );
					jQuery( '.hubwoo-progress-wrap-import' ).show(500);

					var total_prod  = response.total_prod;
					var total_deals = response.total_deals;

					if ( total_prod == 0 ) {
					
						jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).css( 'width', 100 + '%' );
						jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).html( 100 + '%' );
						jQuery( '.hubwoo-progress-wrap-import' ).children( 'p' ).append( '<strong>Completed !</strong>' );		

						if ( total_deals != 0 ) {

							jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', 0 + '%' );
							jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 0 + '%' );
							jQuery( '.hubwoo-progress-wrap-import-deals' ).show(1000);

							await deals_check( total_deals, max_item );
						} else if ( total_deals == 0 ) {
							jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', 100 + '%' );
							jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 100 + '%' );
							jQuery( '.hubwoo-progress-wrap-import-deals' ).show(1000);
							jQuery( '.hubwoo-progress-wrap-import-deals' ).children( 'p' ).append( '<strong>Completed !</strong>' );

							jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsSuccess );
							await saveUpdates( { 'hubwoo_greeting_displayed_setup': 'yes' } );
							setTimeout(function(){ location.reload();}, 3000 );
						}
					
					} else {

						var pro_batches = Math.ceil( total_prod / max_item );
						var batch_count = 1;
						var bar_update = parseFloat( 100 / pro_batches );
						bar_update = parseFloat( bar_update.toFixed(2) );
						var progress_bar = parseFloat( 0 );
						var last_request = false;
						var bulk_pro_response = '';
						var total_deals = '';
						var pro_get_vid = 'process_request';
						
						while ( batch_count <= pro_batches ) {			
							
							progress_bar += bar_update;
  							progress_bar = parseFloat( progress_bar.toFixed(2) );
							
							if ( batch_count == pro_batches ){
								progress_bar = 100;
								last_request = true;
								pro_get_vid = 'final_request';
							}

							bulk_pro_response = await bulkProductsSync( 1, progress_bar, last_request, max_item, pro_get_vid );
							total_deals = bulk_pro_response.total_deals;
							batch_count++;
	

						}
						
						if ( 100 == progress_bar ) {
							jQuery( '.hubwoo-progress-wrap-import' ).children( 'p' ).append( '<strong>Completed !</strong>' );		
							jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', 0 + '%' );
							jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 0 + '%' );
							jQuery( '.hubwoo-progress-wrap-import-deals' ).show(1000);
							
							if ( true == last_request && total_deals == 0 ) {

								jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', 100 + '%' );
								jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 100 + '%' );
								jQuery( '.hubwoo-progress-wrap-import-deals' ).children( 'p' ).append( '<strong>Completed !</strong>' );		
								
							} else {

								await deals_check( total_deals, max_item );
							}
							
						}
					}

				} else if ( response.propertyError == true ) {
					updateProgressBar( 100, 2 );
				} else {
					updateProgressBar( Math.ceil( progress ) );
					// bulkContactSync( parseInt( response.step ), parseInt( response.progress ) );
				}

				return response;
			}

			const bulkProductsSync = async( step, progress, last_request, max_item, pro_get_vid ) => {
				const response = await jQuery.ajax(
					{
						type : 'POST',
						url  : ajaxUrl,
						data : {
							action : 'hubwoo_historical_products_import',
							step,
							hubwooSecurity,
							last_request,
							max_item,
							pro_get_vid,
						},
						dataType : 'json',
					}
				).fail(
					( response ) => {
						jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
						jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
						jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).css( 'width', '100%' );
						jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );
					}
				);

				if ( true == response.status && response.propertyError != true && response.status == true ) {
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).css( 'width', progress + '%' );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).html( progress + '%' );
					jQuery( '.hubwoo-progress-wrap-import' ).show(500);
				
				} else if ( response.propertyError == true ) {
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).css( 'width', '100%' );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );
				} else {
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).css( 'width', '100%' );
					jQuery( '.hubwoo-progress-wrap-import .hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );
				}

				return response;
			}

			const bulkDealsSync = async( step, progress, max_item, deal_get_vid ) => {
				
				const response = await jQuery.ajax(
					{
						type : 'POST',
						url  : ajaxUrl,
						data : {
							action : 'hubwoo_historical_deals_sync',
							step,
							hubwooSecurity,
							max_item,
							deal_get_vid,
						},
						dataType : 'json',
					}
				).fail(
					( response ) => {
						jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
						jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
						jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', '100%' );
						jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );
					}
				);

				if ( true == response.status && response.propertyError != true && response.status == true ) {
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', progress + '%' );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( progress + '%' );
					jQuery( '.hubwoo-progress-wrap-import-deals' ).show(1000);
				
				} else if ( response.propertyError == true ) {
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', '100%' );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );
				} else {
					jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsError );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).addClass( 'hubwoo-progress-error' );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).css( 'width', '100%' );
					jQuery( '.hubwoo-progress-wrap-import-deals .hubwoo-progress-bar' ).html( 'Failed! Please check error log or contact support' );

				}
			}

			const deals_check = async( total_deals, max_item ) => {
			
				var deal_batches = Math.ceil( total_deals / max_item );
				var deal_batch_count = 1;
				var deal_bar_update  = parseFloat( 100 / deal_batches );
				deal_bar_update  = parseFloat( deal_bar_update.toFixed(2) );
				var deal_progress_bar  = parseFloat( 0 );
				var bulk_deal_response = '';
				var deal_get_vid = 'process_request';

				while ( deal_batch_count <= deal_batches ) {
					
					deal_progress_bar += deal_bar_update;
					deal_progress_bar = parseFloat( deal_progress_bar.toFixed(2) );

					if ( deal_batch_count == deal_batches ) {
						deal_progress_bar = 100;
						deal_get_vid = 'final_request';

					}

					bulk_deal_response = await bulkDealsSync( 1, deal_progress_bar, max_item, deal_get_vid );
					deal_batch_count++;

					if ( 100 == deal_progress_bar ) {
						jQuery( '.hubwoo-progress-wrap-import-deals' ).children( 'p' ).append( '<strong>Completed !</strong>' );		
						jQuery( '.hubwoo-progress-notice' ).html( hubwooOcsSuccess );
						await saveUpdates( { 'hubwoo_greeting_displayed_setup': 'yes' } );
						location.reload();
					}

				}
				
			}

		},
	);
}( jQuery ) );
