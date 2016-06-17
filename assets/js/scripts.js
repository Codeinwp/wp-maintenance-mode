jQuery(function($) {
    /**
     * COUNTDOWN
     */
    if ($('.countdown').length > 0) {
        var countDate = new Date($('.countdown').data('start'));
        countDate = new Date($('.countdown').data('end'));
        $('.countdown').countdown({
            until: countDate,
            compact: true,
            layout: '<span class="day">{dn}</span> <span class="separator">:</span> <span class="hour">{hnn}</span> <span class="separator">:</span> <span class="minutes">{mnn}</span> <span class="separator">:</span> <span class="seconds">{snn}</span>'
        });
    }

    /**
     * SOCIAL LINKS
     */
    if ($('.social').length > 0) {
        var link_target = $('.social').data('target');

        if (link_target == 1) {
            $('.social a').attr('target', '_blank');
        }
    }

    /**
     * SUBSCRIBE FORM
     */
    if ($('.subscribe_form').length > 0) {
        // validate form
        $('.subscribe_form').validate({
            submitHandler: function(form) {
                $.post(wpmm_vars.ajax_url, {
                    action: 'wpmm_add_subscriber',
                    email: $('.email_input', $('.subscribe_form')).val()
                }, function(response) {
                    if (!response.success) {
                        alert(response.data);
                        return false;
                    }
                    
                    $('.subscribe_wrapper').html(response.data);
                }, 'json');

                return false;
            }
        });
    }

    /**
     * CONTACT FORM
     */
    if ($('.contact').length > 0) {
        // show form
        $('.contact_us').click(function() {
            var open_contact = $(this).data('open'),
                    close_contact = $(this).data('close');

            $('.contact').fadeIn(200);
            $('.' + open_contact).addClass(close_contact);
        });

        // validate form
        $('.contact_form').validate({
            submitHandler: function(form) {
                $.post(wpmm_vars.ajax_url, {
                    action: 'wpmm_send_contact',
                    name: $('.name_input', $('.contact_form')).val(),
                    email: $('.email_input', $('.contact_form')).val(),
                    content: $('.content_textarea', $('.contact_form')).val()
                }, function(response) {
                    if (!response.success) {
                        alert(response.data);
                        return false;
                    }

                    $('.contact .form').append('<div class="response">' + response.data + '</div>');
                    $('.contact .form .contact_form').hide();

                    setTimeout(function() {
                        $('.contact').hide();
                        $('.contact .form .response').remove();
                        $('.contact .form .contact_form').trigger('reset');
                        $('.contact .form .contact_form').show();
                    }, 2000);
                }, 'json');

                return false;
            }
        });

        // hide form
        $('body').on('click', '.contact', function(e) {
            if ($(e.target).hasClass('contact')) {
                var close_contact = $('.contact_us').data('close');
                $('.form', $(this)).removeClass(close_contact);

                $(this).hide();
            }
        });
    }
});