/* global wp */

jQuery(function ($) {
    /**
     * TABS
     */
    var hash = window.location.hash;
    if (hash !== '') {
        $('.nav-tab-wrapper').children().removeClass('nav-tab-active');
        $('.nav-tab-wrapper a[href="' + hash + '"]').addClass('nav-tab-active');

        // active tab content
        $('.tabs-content').children().addClass('hidden');
        $('.tabs-content div' + hash.replace('#', '#tab-')).removeClass('hidden');

        // trigger `show_{name}_tab_content` event (we use it to refresh codemirror instance on design tab)
        $('body').trigger('show_' + hash.replace('#', '') + '_tab_content');
    }

    $('.nav-tab-wrapper').on('click', 'a', function () {
        var tab_hash = $(this).attr('href'),
                tab_id = tab_hash.replace('#', '#tab-');

        // active tab
        $(this).parent().children().removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // active tab content
        $('.tabs-content').children().addClass('hidden');
        $('.tabs-content div' + tab_id).removeClass('hidden');

        // trigger `show_{name}_tab_content` event (we use it to refresh codemirror instance on design tab)
        $('body').trigger('show_' + tab_hash.replace('#', '') + '_tab_content');
    });

    /**
     * COLOR PICKER
     */
    $('.color_picker_trigger').wpColorPicker();

    /**
     * AVAILABLE SHORTCODES
     */
    $('.shortcodes-list-wrapper').on('click', '.toggle-shortcodes-list', function (e) {
        e.preventDefault();

        var hide_text = $(this).data('hide'),
                show_text = $(this).data('show'),
                list = $(this).next('.shortcodes-list');

        list.toggleClass('show');

        var current_text = list.hasClass('show') ? hide_text : show_text;

        $(this).text(current_text);
    });

    /**
     * CHOSEN.JS MULTISELECT
     * @used for "Backend role" and "Frontend role" -> General tab
     */
    $('.chosen-select').chosen({disable_search_threshold: 10});

    /**
     * IMAGE UPLOADER
     */
    var image_uploaders = {};

    $('body').on('click', '.image_uploader_trigger', function (e) {
        e.preventDefault();

        var name = $(this).data('name') || '',
                title = $(this).data('title') || wpmm_vars.image_uploader_defaults.title,
                button_text = $(this).data('button-text') || wpmm_vars.image_uploader_defaults.button_text,
                to_selector = $(this).data('to-selector') || '';

        if (name === '' || to_selector === '') {
            alert('Required `data` attributes: name, to-selector');
            return;
        }

        // If the uploader object has already been created, reopen the dialog
        if (image_uploaders.hasOwnProperty(name)) {
            image_uploaders[name].open();
            return;
        }

        // Extend the wp.media object
        image_uploaders[name] = wp.media.frames.file_frame = wp.media({
            title: title,
            button: {
                text: button_text
            },
            multiple: false
        });

        // When a file is selected, grab the URL and set it as the text field's value
        image_uploaders[name].on('select', function () {
            var attachment = image_uploaders[name].state().get('selection').first().toJSON();
            var url = attachment.url || '';

            $(to_selector).val(url);
        });

        // Open the uploader dialog
        image_uploaders[name].open();
    });

    /**
     * SHOW DESIGN BACKGROUND TYPE BASED ON SELECTED FIELD
     */
    var show_bg_type = function (selected_val) {
        $('.design_bg_types').hide();
        $('#show_' + selected_val).show();
    };

    show_bg_type($('#design_bg_type').val());

    $('body').on('change', '#design_bg_type', function () {
        var selected_val = $(this).val();

        show_bg_type(selected_val);
    });

    /**
     * PREDEFINED BACKGROUND
     */
    $('ul.bg_list').on('click', 'li', function () {
        $(this).parent().children().removeClass('active');
        $(this).addClass('active');
    });

    /**
     * SUBSCRIBERS EXPORT
     */
    $('#subscribers_wrap').on('click', '#subscribers-export', function () {
        var nonce = $('#tab-modules #_wpnonce').val();
        $('<iframe />').attr('src', wpmm_vars.ajax_url + '?action=wpmm_subscribers_export&_wpnonce='+encodeURI( nonce )).appendTo('body').hide();
    });

    /**
     * SUBSCRIBERS EMPTY LIST
     *
     * @since 2.0.4
     */
    $('#subscribers_wrap').on('click', '#subscribers-empty-list', function () {

        var nonce = $('#tab-modules #_wpnonce').val();

        $.post(wpmm_vars.ajax_url, {
            action: 'wpmm_subscribers_empty_list',
            _wpnonce: nonce
        }, function (response) {
            if (!response.success) {
                alert(response.data);
                return false;
            }

            $('#subscribers_wrap').html(response.data);
        }, 'json');
    });

    /**
     * RESET SETTINGS
     */
    $('body').on('click', '.reset_settings', function () {
        var tab = $(this).data('tab'),
                nonce = $('#tab-' + tab + ' #_wpnonce').val();

        $.post(wpmm_vars.ajax_url, {
            action: 'wpmm_reset_settings',
            tab: tab,
            _wpnonce: nonce
        }, function (response) {
            if (!response.success) {
                alert(response.data);
                return false;
            }

            window.location.reload(true);
        }, 'json');
    });

    /**
     * COUNTDOWN TIMEPICKER
     */
    $('.countdown_start').datetimepicker({timeFormat: 'HH:mm:ss', dateFormat: 'dd-mm-yy'});

    /**
     * TEMPLATES
     */
    $('#dashboard-import-button').on('click', '.button-import', function () {
        const nonce = $('#tab-design #_wpnonce').val();
        const templateSlug = $('input[name="template"]:checked').val();

        import_template( templateSlug, 'tab-design', nonce, function( data ) {
            window.location.href = data['pageEditURL'].replace(/&amp;/g, '&');
        } );
    });

    $('select[name="options[design][page_id]"]').on('change', function () {
        const nonce = $('#tab-design #_wpnonce').val();

        $.post(wpmm_vars.ajax_url, {
            action: 'wpmm_select_page',
            page_id: this.value,
            _wpnonce: nonce
        }, function (response) {
            if (!response.success) {
                alert(response.data);
                return false;
            }

            window.location.reload();
        }, 'json');
    });

    /**
     * WIZARD
     */
    if ( $('input[name="wizard-template"]:checked').val() ) {
        $('#wpmm-wizard-wrapper .button-import').removeClass('disabled');
    } else {
        $('input[name="wizard-template"]').on('change', function () {
            $('#wpmm-wizard-wrapper .button-import').removeClass('disabled');
            $('input[name="wizard-template"]').off('change');
        });
    }

    if ( $('input[name="dashboard-template"]:checked').val() ) {
        $('#dashboard-import-button .button-import').removeClass('disabled');
    } else {
        $('input[name="dashboard-template"]').on('change', function () {
            $('#dashboard-import-button .button-import').removeClass('disabled');
            $('input[name="dashboard-template"]').off('change');
        });
    }

    $('#wizard-import-button').on('click', '.button-import:not(.disabled)', function() {
        const templateSlug = $('input[name="wizard-template"]:checked').val();

        import_template( templateSlug, 'wizard', wpmm_vars.wizard_nonce, function () {
            window.location.reload(true);
        } );
    })

    function import_template ( slug, source, nonce, callback ) {
        $('.button-import').addClass( 'loading' );
        $('.button-import').attr( 'disabled', 'disabled' );
        $('<span class="dashicons dashicons-update"></span>').insertAfter( $('.button-import') );

        if ( ! wpmm_vars.is_otter_installed ) {
            install_and_activate_otter( () => add_to_page(slug, nonce, source, callback) );
        } else if ( ! wpmm_vars.is_otter_activated ) {
            activate_otter( () => add_to_page(slug, nonce, source, callback) );
        }
    }

    function add_to_page(slug, nonce, source, callback) {
        $.post(wpmm_vars.ajax_url, {
            action: 'wpmm_insert_template',
            template_slug: slug,
            _wpnonce:  nonce,
            source: source,
        }, function(response) {
            if (!response.success) {
                console.log(response.data);
                return false;
            }

            $('.button-import').removeClass( 'loading' );
            $('.button-import').removeAttr( 'disabled' );
            $('.dashicons-update').remove();

            callback( response.data );
        }, 'json');
    }

    function install_and_activate_otter( callback ) {
        jQuery.post(wpmm_vars.ajax_url, {
            action: 'wp_ajax_install_plugin',
            _ajax_nonce: wpmm_vars.plugin_install_nonce,
            slug: 'otter-blocks',
        }, function(response) {
            if (!response.success) {
                console.log(response.data);
                return false;
            }

            activate_otter( callback );
        });
    }

    function activate_otter( callback ) {
        jQuery.get( wpmm_vars.otter_activation_link, function() {
            callback();
        } )
    }
});
