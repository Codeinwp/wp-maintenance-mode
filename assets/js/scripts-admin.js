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

});