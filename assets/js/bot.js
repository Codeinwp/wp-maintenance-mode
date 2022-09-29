/* global jQuery, botVars */

const DEBUG = false;

// User info retrieved from chats is store in this object
const context = {};

// Cache conversation position
let conversationPos;

// Cache conversation status
let conversationStarted = false;

/*
 ---------------
 Utility Functions
 ---------------
 */

/**
 * Cast string
 *
 * @param  value
 * @return string
 */
function _phpCastString( value ) {
	const type = typeof value;

	switch ( type ) {
		case 'boolean':
			return value ? '1' : '';
		case 'string':
			return value;
		case 'number':
			if ( isNaN( value ) ) {
				return 'NAN';
			}

			if ( ! isFinite( value ) ) {
				return ( value < 0 ? '-' : '' ) + 'INF';
			}

			return value + '';
		case 'undefined':
			return '';
		case 'object':
			if ( Array.isArray( value ) ) {
				return 'Array';
			}

			if ( value !== null ) {
				return 'Object';
			}

			return '';
		case 'function':
			// fall through
		default:
			throw new Error( 'Unsupported value type' );
	}
}

/**
 * Strip tags from a string
 *
 * big thanks to http://locutus.io/php/strings/strip_tags/
 *
 * @param  string  input
 * @param  string  allowed
 * @param  input
 * @param  allowed
 * @return string
 */
function stripTags( input, allowed ) {
	// making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	allowed = ( ( ( allowed || '' ) + '' ).toLowerCase().match( /<[a-z][a-z0-9]*>/g ) || [] ).join( '' );

	const tags = /<\/?([a-z0-9]*)\b[^>]*>?/gi;
	const commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

	let after = _phpCastString( input );
	// removes tha '<' char at the end of the string to replicate PHP's behaviour
	after = ( after.substring( after.length - 1 ) === '<' ) ? after.substring( 0, after.length - 1 ) : after;

	// recursively remove tags to ensure that the returned string doesn't contain forbidden tags after previous passes (e.g. '<<bait/>switch/>')
	while ( true ) {
		const before = after;
		after = before.replace( commentsAndPhpTags, '' ).replace( tags, function( $0, $1 ) {
			return allowed.indexOf( '<' + $1.toLowerCase() + '>' ) > -1 ? $0 : '';
		} );

		// return once no more tags are removed
		if ( before === after ) {
			return after;
		}
	}
}

function renderStatement( statement ) {
	// Strip html tags from statement
	statement = stripTags( statement );

	jQuery( '.chat-container' ).append( '<div class="chat-message-wrapper"><p class="chat-message">' + statement + '</p></div>' );
}

function showTyping() {
	jQuery( '.chat-container' ).append( '<div class="typing-wrapper"><p class="chat-message typing"><span class="dot"></span><span class="dot"></span><span class="dot"></span></p></div>' );
}

function hideTyping() {
	jQuery( '.typing-wrapper' ).remove();
}

const chatWrapper = jQuery( '.bot-chat-wrapper' );

function scrollToBottom() {
	chatWrapper.animate( {
		scrollTop: 600,
	}, 'slow' );
}

/**
 * Input checking
 *
 * @param  msg
 */
function inputError( msg ) {
	jQuery( '.bot-error p' ).text( msg );
	jQuery( '.bot-error' )
		.animate( { bottom: '20px' }, 500 )
		.delay( 3000 )
		.animate( { bottom: '-70px' }, 500 );
}

function checkInput( option ) {
	let input = jQuery( '.bot-container input[type=text]' ).val();

	// Strip html tags from input
	input = stripTags( input );

	if ( input.length > 2 ) {
		showResponse( option );
	} else {
		inputError( botVars.validationName );
	}

	return false;
}

function checkEmail( option ) {
	const input = jQuery( '.bot-container input[type=email]' ).val();
	const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	const result = regex.test( String( input.toLowerCase() ) );

	if ( input.length > 7 && result === true ) {
		// Add new entry to our db
		const botSubscribeForm = jQuery( '.bot_subscribe_form' ).serialize();
		const subscribeBotData = 'action=wpmm_add_subscriber&' + botSubscribeForm;

		jQuery.post( wpmmVars.ajaxURL, subscribeBotData, function( response ) {
			if ( ! response.success ) {
				alert( response.data );
				return false;
			}
		}, 'json' );

		showResponse( option );
	} else {
		inputError( botVars.validationEmail );
	}
	return false;
}

function clearChat() {
	jQuery( '.chat-container' ).empty();
}

function clearFooter() {
	jQuery( '.choices' ).empty();
	jQuery( '.input' ).empty();
}

/*
 ------------------------
 Setup Conversation Data
 ------------------------
 */

function startConversation( conv, pos ) {
	/* We need this to maintain backward compatibility because we moved startConversation() from /views/maintenance.php to bot.js */
	if ( conversationStarted ) {
		return false;
	}

	conversationStarted = true;

	clearFooter();
	clearChat();

	// Set conversation position
	// 'conversation' is in the global scope
	conversationPos = conv;

	// Load conversation data
	jQuery.getScript( botVars.uploadsBaseUrl + 'data.js', function( data ) {
		// Show first bot statement
		showStatement( pos );
	} );
}

/*
 -------------------
 Show Bot Statement
 -------------------
 */
function showStatement( pos ) {
	// Where are we in conversationData?
	const node = conversationData[ conversationPos ][ pos ];

	// If there is a side effect execute that within the context
	if ( 'sideeffect' in node && jQuery.type( node.sideeffect === 'function' ) ) {
		node.sideeffect( context );
	}

	// Wrap the statements in an array (if they're not already)
	let statements;
	if ( jQuery.type( node.statement ) === 'array' ) {
		statements = node.statement;
	} else if ( jQuery.type( node.statement ) === 'string' ) {
		statements = [ node.statement ];
	} else if ( jQuery.type( node.statement ) === 'function' ) {
		statements = node.statement( context );
	}

	if ( pos === 1.5 || pos === 1.6 ) {
		jQuery( '.avatar-notice' ).remove();
	}

	/*
     ------------------------
     Render Bot Statement(s)
     ------------------------
     Run this function over each statement
     */
	async.eachSeries( statements, function( item, callback ) {
		// Emulate typing then scroll to bottom
		showTyping();
		scrollToBottom();

		let delay = 900;

		if ( DEBUG || ! jQuery( '.bot-chat-wrapper' )[ 0 ] ) {
			delay = 0;
		}

		setTimeout( function() {
			hideTyping();
			renderStatement( item );
			scrollToBottom();

			callback();
		}, delay );
	},
	/*
             ----------------------
             Render User Option(s)
             ----------------------
             This is the final callback of the series
             */
	function( err ) {
		/*
                         ----------------------------
                         If User Option is Button(s)
                         ----------------------------
                         */
		if ( 'options' in node ) {
			jQuery( '.input' ).hide();
			jQuery( '.choices' ).show();

			// Get the options' data
			const options = node.options;

			// If there are options render them
			// Otherwise this is the end
			if ( options.length > 0 ) {
				// Pause 750ms, then render options
				setTimeout( function() {
					for ( let i = 0; i < options.length; i++ ) {
						const option = options[ i ];
						var extraClass;
						var clickFunction;

						// Check option for a consequence
						if ( option.consequence === null ) {
							// The consequence is null meaning this is a branch we won't be exploring
							// The button is given class 'disabled' and does nothing on click
							clickFunction = null;
							extraClass = 'disabled';
						} else {
							// Else, click function (showResponse) is binded to it
							clickFunction = function( option ) {
								showResponse( option );
							}.bind( null, option );

							extraClass = '';
						}

						// Render button
						const button = jQuery( '<p/>', {
							text: option.choice,
							class: 'chat-message user',
							click: clickFunction,
						} ).appendTo( '.choices' );
					}
				}, 750 );
			}

			/*
                             ------------------------
                             If User Option is Input
                             ------------------------
                             */
		} else if ( 'input' in node ) {
			jQuery( '.input' ).show();
			jQuery( '.choices' ).hide();

			var option = node.input;

			/*
                             Render Input
                             ---------------
                             */

			// Create a form to hold our input and submit button
			var form = jQuery( '<form/>', {
				submit: checkInput.bind( null, option ),
			} );

			// Create a user bubble, append to form
			var inputBubble = jQuery( '<p/>', {
				class: 'chat-message user',
			} ).appendTo( form );

			// Create an input, append to user bubble
			var input = jQuery( '<input/>', {
				type: 'text',
				placeholder: botVars.typeName,
				name: option.name,
				autocomplete: 'off',
				required: true,
			} ).appendTo( inputBubble );

			// Create an input button, append to user bubble
			var button = jQuery( '<a/>', {
				text: botVars.send,
				click: checkInput.bind( null, option ),
			} ).appendTo( inputBubble );

			// Append form to div.input
			form.appendTo( '.input' );

			// Focus on the input we just put into the DOM
			async.nextTick( function() {
				input.focus();
			} );

			/*
                             ------------------------
                             If User Option is Email
                             ------------------------
                             */
		} else if ( 'email' in node ) {
			jQuery( '.input' ).show();
			jQuery( '.choices' ).hide();

			var option = node.email;

			/*
                             Render Input
                             ---------------
                             */

			// Create a form to hold our input and submit button
			var form = jQuery( '<form/>', {
				class: 'bot_subscribe_form',
				submit: checkEmail.bind( null, option ),
			} );

			// Create a user bubble, append to form
			var inputBubble = jQuery( '<p/>', {
				class: 'chat-message user',
			} ).appendTo( form );

			// Create hidden input, append to user bubble
			var input = jQuery( '<input/>', {
				type: 'hidden',
				name: '_wpnonce',
				value: botVars.wpnonce,
			} ).appendTo( inputBubble );

			// Create email input, append to user bubble
			var input = jQuery( '<input/>', {
				type: 'email',
				placeholder: botVars.typeEmail,
				name: option.email,
				autocomplete: 'off',
			} ).appendTo( inputBubble );

			// Create an input button, append to user bubble
			var button = jQuery( '<a/>', {
				text: botVars.send,
				// "class": "user-email-trigger",
				click: checkEmail.bind( null, option ),
			} ).appendTo( inputBubble );

			// Append form to div.input
			form.appendTo( '.input' );

			// Focus on the input we just put into the DOM
			async.nextTick( function() {
				input.focus();
			} );
		}

		scrollToBottom();
	} );
}

/*
 ---------------------
 Render User Response
 ---------------------
 */
function showResponse( option ) {
	// If there was an input element, put that into the global context
	let feedback = '';

	if ( 'name' in option ) {
		context[ option.name ] = jQuery( '.bot-container input[type=text]' ).val();
		feedback = context[ option.name ];
	} else if ( 'email' in option ) {
		context[ option.email ] = jQuery( '.bot-container input[type=email]' ).val();
		feedback = context[ option.email ];
	} else {
		feedback = option.choice;
	}

	clearFooter();

	// Strip html tags from feedback
	feedback = stripTags( feedback );

	// Show what the user chose
	jQuery( '.chat-container' ).append( '<p class="chat-message user">' + feedback + '</p>' );

	if ( 'consequence' in option ) {
		showStatement( option.consequence );
	} else {
		// xxx
	}
}

/*
 -------------------
 Initialize Conversation
 -------------------
 */

let isOpen = false;

jQuery( '.bot-avatar' ).on( 'click', function() {
	const chatWrap = jQuery( '.bot-chat-wrapper' )[ 0 ];

	if ( ! isOpen ) {
		isOpen = true;
		startConversation( 'homepage', 1 );
	}

	if ( chatWrap.style.display !== 'none' ) {
		jQuery( '.avatar-notice' ).show();
		jQuery( '.bot-chat-wrapper' ).hide();
	} else {
		jQuery( '.bot-chat-wrapper' ).show();
		jQuery( '.avatar-notice' ).hide();
	}
} );
