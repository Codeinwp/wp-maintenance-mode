/* global jQuery, wpmmVars */

jQuery( function( $ ) {
	function isEmailValid( email ) {
		const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		const result = regex.test( String( email.toLowerCase() ) );

		return email.length > 7 && result === true;
	}

	/**
	 * Migration & Rollback
	 */
	$( '#wpmm-migrate, #wpmm-rollback' ).on( 'click', function() {
		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_toggle_gutenberg',
			source: this.parentElement.parentElement.dataset.key,
			_wpnonce: this.parentElement.parentElement.dataset.nonce,
		}, function( response ) {
			if ( ! response.success ) {
				alert( response );
				return false;
			}

			window.location.reload();
		}, 'json' );
	} );

	/**
	 * TABS
	 */
	const hash = window.location.hash;
	if ( hash !== '' ) {
		$( '.nav-tab-wrapper' ).children().removeClass( 'nav-tab-active' );
		$( '.nav-tab-wrapper a[href="' + hash + '"]' ).addClass( 'nav-tab-active' );

		// active tab content
		$( '.tabs-content' ).children().addClass( 'hidden' );
		$( '.tabs-content div' + hash.replace( '#', '#tab-' ) ).removeClass( 'hidden' );

		// trigger `show_{name}_tab_content` event (we use it to refresh codemirror instance on design tab)
		$( 'body' ).trigger( 'show_' + hash.replace( '#', '' ) + '_tab_content' );
	}

	$( '.nav-tab-wrapper' ).on( 'click', 'a', function() {
		const tabHash = $( this ).attr( 'href' ),
			tabId = tabHash.replace( '#', '#tab-' );

		// active tab
		$( this ).parent().children().removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-active' );

		// active tab content
		$( '.tabs-content' ).children().addClass( 'hidden' );
		$( '.tabs-content div' + tabId ).removeClass( 'hidden' );

		// trigger `show_{name}_tab_content` event (we use it to refresh codemirror instance on design tab)
		$( 'body' ).trigger( 'show_' + tabHash.replace( '#', '' ) + '_tab_content' );
	} );

	/**
	 * COLOR PICKER
	 */
	$( '.color_picker_trigger' ).wpColorPicker();

	/**
	 * AVAILABLE SHORTCODES
	 */
	$( '.shortcodes-list-wrapper' ).on( 'click', '.toggle-shortcodes-list', function( e ) {
		e.preventDefault();

		const hideText = $( this ).data( 'hide' ),
			showText = $( this ).data( 'show' ),
			list = $( this ).next( '.shortcodes-list' );

		list.toggleClass( 'show' );

		const currentText = list.hasClass( 'show' ) ? hideText : showText;

		$( this ).text( currentText );
	} );

	/**
	 * CHOSEN.JS MULTISELECT
	 *
	 * @used for "Backend role" and "Frontend role" -> General tab
	 */
	$( '.chosen-select' ).chosen( { disable_search_threshold: 10 } );

	/**
	 * IMAGE UPLOADER
	 */
	const imageUploaders = {};

	$( 'body' ).on( 'click', '.image_uploader_trigger', function( e ) {
		e.preventDefault();

		const name = $( this ).data( 'name' ) || '',
			title = $( this ).data( 'title' ) || wpmmVars.imageUploaderDefaults.title,
			buttonText = $( this ).data( 'button-text' ) || wpmmVars.imageUploaderDefaults.buttonText,
			toSelector = $( this ).data( 'to-selector' ) || '';

		if ( name === '' || toSelector === '' ) {
			alert( 'Required `data` attributes: name, to-selector' );
			return;
		}

		// If the uploader object has already been created, reopen the dialog
		if ( imageUploaders.hasOwnProperty( name ) ) {
			imageUploaders[ name ].open();
			return;
		}

		// Extend the wp.media object
		imageUploaders[ name ] = wp.media.frames.file_frame = wp.media( {
			title,
			button: {
				text: buttonText,
			},
			multiple: false,
		} );

		// When a file is selected, grab the URL and set it as the text field's value
		imageUploaders[ name ].on( 'select', function() {
			const attachment = imageUploaders[ name ].state().get( 'selection' ).first().toJSON();
			const url = attachment.url || '';

			$( toSelector ).val( url );
		} );

		// Open the uploader dialog
		imageUploaders[ name ].open();
	} );

	/**
	 * SHOW DESIGN BACKGROUND TYPE BASED ON SELECTED FIELD
	 *
	 * @param  selectedVal
	 */
	const showBgType = function( selectedVal ) {
		$( '.design_bg_types' ).hide();
		$( '#show_' + selectedVal ).show();
	};

	showBgType( $( '#design_bg_type' ).val() );

	$( 'body' ).on( 'change', '#design_bg_type', function() {
		const selectedVal = $( this ).val();

		showBgType( selectedVal );
	} );

	/**
	 * PREDEFINED BACKGROUND
	 */
	$( 'ul.bg_list' ).on( 'click', 'li', function() {
		$( this ).parent().children().removeClass( 'active' );
		$( this ).addClass( 'active' );
	} );

	/**
	 * SUBSCRIBERS EXPORT
	 */
	$( '#subscribers_wrap' ).on( 'click', '#subscribers-export', function() {
		const nonce = $( '#tab-modules #_wpnonce' ).val();
		$( '<iframe />' ).attr( 'src', wpmmVars.ajaxURL + '?action=wpmm_subscribers_export&_wpnonce=' + encodeURI( nonce ) ).appendTo( 'body' ).hide();
	} );

	/**
	 * SUBSCRIBERS EMPTY LIST
	 *
	 * @since 2.0.4
	 */
	$( '#subscribers_wrap' ).on( 'click', '#subscribers-empty-list', function() {
		const nonce = $( '#tab-modules #_wpnonce' ).val();

		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_subscribers_empty_list',
			_wpnonce: nonce,
		}, function( response ) {
			if ( ! response.success ) {
				alert( response.data );
				return false;
			}

			$( '#subscribers_wrap' ).html( response.data );
		}, 'json' );
	} );

	/**
	 * RESET SETTINGS
	 */
	$( 'body' ).on( 'click', '.reset_settings', function() {
		const tab = $( this ).data( 'tab' ),
			nonce = $( '#tab-' + tab + ' #_wpnonce' ).val();

		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_reset_settings',
			tab,
			_wpnonce: nonce,
		}, function( response ) {
			if ( ! response.success ) {
				alert( response.data );
				return false;
			}

			window.location.reload( true );
		}, 'json' );
	} );

	/**
	 * COUNTDOWN TIMEPICKER
	 */
	$( '.countdown_start' ).datetimepicker( { timeFormat: 'HH:mm:ss', dateFormat: 'dd-mm-yy' } );

	/**
	 * TEMPLATES
	 */
	let pageEditURL = '#';
	const templateWrap = $( '.wpmm-template-image-wrap' );
	const wizardButtons = $( '#wizard-buttons' );

	templateWrap.on( 'click', '.button-import', function() {
		/* If the page has some content inside, show a confirmation prompt to let the use know the
		content will be replaced and fire the import only after confirmation */
		if ( this.dataset.replace !== '0' ) {
			openModal( {
				title: wpmmVars.confirmModalTexts.title,
				description: wpmmVars.confirmModalTexts.description,
				first_button: `<button class="button button-primary button-big confirm button-import">${ wpmmVars.confirmModalTexts.buttonContinue }</button>`,
				second_button: `<button class="button button-secondary button-big go-back">${ wpmmVars.confirmModalTexts.buttonGoBack }</button>`,
			} );

			$( this ).parent().addClass( 'importing' );
			$( this ).hide();

			const importButton = this;
			$( 'button.confirm' ).on( 'click', function() {
				$( this ).html( '<span class="dashicons dashicons-update"></span>' + wpmmVars.importingText + '...' );
				$( '.modal-content' ).find( $( '.go-back' ) ).addClass( 'disabled' );
				fireImport( importButton );
			} );

			$( 'button.go-back' ).on( 'click', function() {
				$( '.modal-overlay' ).remove();
				$( 'body' ).removeClass( 'has-modal' );
				$( importButton ).show();
				$( importButton ).parent().removeClass( 'importing' );
			} );
		} else {
			fireImport( this );
		}
	} );

	function fireImport( button ) {
		const nonce = $( '#tab-design #_wpnonce' ).val();
		const templateSlug = button.dataset.slug;
		const category = button.dataset.category;

		const data = {
			_wpnonce: nonce,
			source: 'tab-design',
			template_slug: templateSlug,
			category,
		};

		$( button ).html( '<span class="dashicons dashicons-update"></span>' + wpmmVars.importingText + '...' );
		$( '.button-import' ).addClass( 'disabled' ).css( 'pointer-events', 'none' );

		templateWrap.removeClass( 'can-import' );
		$( button ).parent().addClass( 'importing' );

		importTemplate( data, function( response ) {
			pageEditURL = response.pageEditURL.replace( /&amp;/g, '&' );

			$( '.importing .button-import' ).html( wpmmVars.importDone );
			openModal( {
				title: wpmmVars.modalTexts.title,
				description: wpmmVars.modalTexts.description,
				first_button: `<a href="${ pageEditURL }" class="button button-primary button-big">${ wpmmVars.modalTexts.buttonPage }</a>`,
				second_button: `<button class="button button-secondary button-big" onClick="window.location.reload()">${ wpmmVars.modalTexts.buttonSettings }</button>`,
			} );
		} );
	}

	function openModal( content ) {
		const modalOverlay = $(
			'<div class="modal-overlay">' +
                '<div class="modal-frame">' +
                    '<div class="modal-content">' +
                        `<h4 class="modal-header">${ content.title }</h4>` +
                        `<p class="modal-text">${ content.description }</p>` +
                        '<div class="buttons-wrap">' +
                            content.first_button +
                            content.second_button +
                        '</div>' +
                    '</div>' +
				'</div>' +
            '</div>'
		);

		$( 'body' ).addClass( 'has-modal' );

		if ( $( '.modal-overlay' ).length ) {
			$( '.modal-overlay' ).replaceWith( modalOverlay );
		} else {
			$( modalOverlay ).appendTo( 'body' );
		}
	}

	$( 'select[name="options[design][template_category]"]' ).on( 'change', function() {
		const nonce = $( '#tab-design #_wpnonce' ).val();

		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_change_template_category',
			category: this.value,
			_wpnonce: nonce,
		}, function( response ) {
			if ( ! response.success ) {
				alert( response );
				return false;
			}

			window.location.reload();
		}, 'json' );
	} );

	$( 'select[name="options[design][page_id]"]' ).on( 'change', function() {
		const nonce = $( '#tab-design #_wpnonce' ).val();

		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_select_page',
			page_id: this.value,
			_wpnonce: nonce,
		}, function( response ) {
			if ( ! response.success ) {
				alert( response.data );
				return false;
			}

			window.location.reload();
		}, 'json' );
	} );

	/**
	 * WIZARD
	 */
	let skipWizard = false;
	const wizardTemplateSelect = $( 'input[name="wizard-template"]' );
	const wizardImportButton = $( '#wpmm-wizard-wrapper .button-import' );

	$( 'h2.wpmm-title span' ).on( 'click', function() {
		window.location.href = wpmmVars.adminURL;
	} );

	if ( $( 'input[name="wizard-template"]:checked' ).val() ) {
		$( '#wpmm-wizard-wrapper .button-import' ).removeClass( 'disabled' );
	}

	wizardTemplateSelect.on( 'change', function() {
		wizardImportButton.removeClass( 'disabled' );
	} );

	wizardButtons.on( 'click', '.button-import:not(.disabled)', function() {
		const templateSelect = $( 'input[name="wizard-template"]:checked' );
		const templateSlug = templateSelect.val();
		const category = templateSelect[ 0 ].dataset.category;

		const data = {
			_wpnonce: wpmmVars.wizardNonce,
			source: 'wizard',
			template_slug: templateSlug,
			category,
		};

		importInProgress( data.template_slug );
		importTemplate( data, function( response ) {
			moveToStep( 'import', 'subscribe' );
			pageEditURL = response.pageEditURL.replace( /&amp;/g, '&' );

			$( '#wpmm-wizard-wrapper .finish-step .heading' ).text( wpmmVars.finishWizardStrings[ category ] );
			$( '#wpmm-wizard-wrapper .button-skip' ).removeClass( 'disabled' );
		} );
	} );

	wizardButtons.on( 'click', '.button-skip', function() {
		$( this ).addClass( 'is-busy' );
		$( this ).trigger( 'blur' );

		handleOptimole().then( function() {
			$.post( wpmmVars.ajaxURL, {
				action: 'wpmm_skip_wizard',
				_wpnonce: wpmmVars.wizardNonce,
			}, function( response ) {
				if ( ! response.success ) {
					addErrorMessage();
					return;
				}

				skipWizard = true;
				moveToStep( 'import', 'subscribe' );
			} );
		} ).catch( function() {
			addErrorMessage();
			$( '.button-skip' ).removeClass( 'is-busy' );
		} );
	} );

	$( '#email-input-wrap input[type="text"]' ).on( 'keypress', ( e ) => {
		if ( e.key === 'Enter' ) {
			const button = $( '#email-input-wrap .subscribe-button' );
			button.trigger( 'click' );
		}
	} );

	$( '#email-input-wrap' ).on( 'click', '.subscribe-button', function( event ) {
		event.preventDefault();

		const emailInput = $( '#email-input-wrap input[type="text"]' );
		const email = emailInput.val();

		if ( ! isEmailValid( email ) ) {
			$( '#email-input-wrap' ).append( `<p class="subscribe-message email-error"><i>${ wpmmVars.invalidEmailString }</i></p>` );
			emailInput.addClass( 'invalid' );

			setTimeout( function() {
				$( '.email-error' ).remove();
			}, 1500 );

			return;
		}

		const subscribeButton = $( this );

		emailInput.removeClass( 'invalid' );
		subscribeButton.addClass( 'is-busy' );

		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_subscribe',
			email,
			_wpnonce: wpmmVars.wizardNonce,
		}, function( response ) {
			if ( ! response.success ) {
				alert( response.data );
			}

			subscribeButton.removeClass( 'is-busy' );
			if ( ! skipWizard ) {
				moveToStep( 'subscribe', 'finish' );
				return;
			}

			window.location.reload();
		} );

		return false;
	} );

	$( '#skip-subscribe' ).on( 'click', function() {
		if ( ! skipWizard ) {
			moveToStep( 'subscribe', 'finish' );
			return;
		}

		window.location.reload();
	} );

	$( '#view-page-button' ).on( 'click', function() {
		window.location.href = pageEditURL;
	} );

	$( '#refresh-button' ).on( 'click', function() {
		window.location.reload();
	} );

	$( document ).on( 'change', 'input.wpmm_network_mode', function() {
        var status = $( this ).parents( 'table' ).find( '.wpmm_status' );
        if ( status.hasClass( 'wpmm_status_disable' ) ) {
            status.removeClass( 'wpmm_status_disable' );
        } else {
            status.addClass( 'wpmm_status_disable' );
        }
    } );

	/**
	 * Adds elements and CSS when importing from wizard
	 *
	 * @param {string} slug The template that will be imported
	 */
	function importInProgress( slug ) {
		const template = $( 'input[value=' + slug + '] + .wpmm-template' );

		template.addClass( 'loading' );
		template.append( '<span class="dashicons dashicons-update"></span><p><i>' + wpmmVars.loadingString + '</i></p>' );

		$( '.button-import' ).attr( 'disabled', 'disabled' );
		$( '#wpmm-wizard-wrapper .button-skip' ).addClass( 'disabled' );
		$( '#wpmm-wizard-wrapper .wpmm-templates-radio label' ).css( 'pointer-events', 'none' );
	}

	/**
	 * Goes to the next step from wizard
	 *
	 * @param {string} prevStep
	 * @param {string} nextStep
	 */
	function moveToStep( prevStep, nextStep ) {
		$( '.slider-wrap' ).removeClass( `move-to-${ prevStep }` ).addClass( `move-to-${ nextStep }` );

		$( `.${ prevStep }-step` ).attr( 'aria-hidden', 'true' ).css( 'display', 'none' );
		$( `.${ nextStep }-step` ).removeAttr( 'aria-hidden' ).removeAttr( 'style' );
	}

	/**
	 * Installs or activates Otter and Optimole and adds the template after
	 *
	 * @param {Object}   data
	 * @param {Function} callback
	 */
	function importTemplate( data, callback ) {
		handleOtter()
			.then( () => handleOptimole() )
			.then( () => addToPage( data, callback ) )
			.catch( ( error ) => {
				// eslint-disable-next-line no-console
				console.error( error );

				const template = $( '.wpmm-template.loading' );

				template.removeClass( 'loading' );
				$( '.wpmm-template .dashicons.dashicons-update' ).remove();
				$( '.wpmm-template p' ).remove();

				$( '.button-import' ).removeAttr( 'disabled' );
				$( '#wpmm-wizard-wrapper .button-skip' ).removeClass( 'disabled' );
				$( '#wpmm-wizard-wrapper .wpmm-templates-radio label' ).removeAttr( 'style' );

				addErrorMessage();
			} );
	}

	/**
	 * Displays an error message
	 */
	function addErrorMessage() {
		if ( $( '#import-step-error' ).length ) {
			return;
		}

		$( '.import-step' ).append( `<p id="import-step-error">${ wpmmVars.errorString }</p>` );
		setTimeout( function() {
			$( '#import-step-error' ).remove();
		}, 3000 );
	}

	/**
	 * Adds the template content to the Maintenance Page
	 * and calls the callback after
	 *
	 * @param {Object}   data
	 * @param {Function} callback
	 */
	function addToPage( data, callback ) {
		data.action = 'wpmm_insert_template';
		$.post( wpmmVars.ajaxURL, data, function( response ) {
			if ( ! response.success ) {
				alert( response.data.error );
				$( '.dashicons-update' ).remove();
				addErrorMessage();
				return false;
			}

			callback( response.data );
		}, 'json' );
	}

	/**
	 * Install and activate Optimole if the checkbox is checked.
	 */
	function handleOptimole() {
		const optimoleCheckbox = $( '#wizard-optimole-checkbox' );

		if ( optimoleCheckbox.length && optimoleCheckbox.is( ':checked' ) ) {
			if ( ! wpmmVars.isOptimoleInstalled ) {
				return installPlugin( 'optimole-wp' )
					.then( () => {
						return activatePlugin( 'optimole-wp' );
					} );
			} else if ( ! wpmmVars.isOptimoleActive ) {
				return activatePlugin( 'optimole-wp' );
			}
		}

		return Promise.resolve();
	}

	/**
	 * Install and activate Otter.
	 */
	function handleOtter() {
		if ( ! wpmmVars.isOtterInstalled ) {
			return installPlugin( 'otter-blocks' )
				.then( () => {
					updateSDKOptions();
					return activatePlugin( 'otter-blocks' );
				} );
		} else if ( ! wpmmVars.isOtterActivated ) {
			return activatePlugin( 'otter-blocks' );
		}

		return Promise.resolve();
	}

	/**
	 * Updates options to track Otter traffic.
	 * Fires when Otter is installed.
	 */
	function updateSDKOptions() {
		$.post( wpmmVars.ajaxURL, {
			action: 'wpmm_update_sdk_options',
			_wpnonce: wpmmVars.ajaxNonce,
		}, function( response ) {
			if ( ! response.success ) {
				// eslint-disable-next-line no-console
				console.error( response.data );
			}
		} );
	}

	/**
	 * Install a plugin.
	 *
	 * @param {string} slug
	 */
	function installPlugin( slug ) {
		return $.post( wpmmVars.ajaxURL, {
			action: 'wp_ajax_install_plugin',
			_ajax_nonce: wpmmVars.pluginInstallNonce,
			slug,
		}, function( response ) {
			if ( ! response.success ) {
				addErrorMessage();
				return false;
			}
		} );
	}

	/**
	 * Activate a plugin.
	 *
	 * @param {string} slug
	 */
	function activatePlugin( slug ) {
		switch ( slug ) {
			case 'otter-blocks':
				return $.get( wpmmVars.otterActivationLink );
			case 'optimole-wp':
				return $.get( wpmmVars.optimoleActivationLink );
			default:
				break;
		}
	}
} );
