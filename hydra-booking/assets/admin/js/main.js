(function ($) {

    $(document).ready(function () { 
        // hydra-booking-insights-data-we-collect hide 
        $('.hydra-booking-insights-data-we-collect').parents('.updated').find('p.description').hide();

    });


    jQuery(document).ready(function($) {
        $('.tfhb-plugin-button').not('.pro').on('click', function(e) {
            e.preventDefault();
           
            let button = $(this);
            let action = button.data('action');
            let pluginSlug = button.data('plugin');
            let pluginFileName = button.data('plugin_filename');
    
            if (!action || !pluginSlug) return;
    
            let loader = button.find('.loader');
            let originalText = button.clone().children().remove().end().text().trim();
    
            if (action === 'install') {
                button.contents().first().replaceWith('Installing..');
            } else if (action === 'activate') {
                button.contents().first().replaceWith('Activating..');
            }
    
            button.addClass('loading').prop('disabled', true);
            loader.show();
    
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tfhb_hydra_manage_plugin',
                    security: tfhb_core_apps.rest_nonce,
                    plugin_slug: pluginSlug,
                    plugin_filename: pluginFileName,
                    plugin_action: action
                },
                success: function(response) {
                    button.removeClass('loading').prop('disabled', false);
                    loader.hide();
    
                    if (response.success) {
                        if (action === 'install') {
                            button.contents().first().replaceWith('Activate');
                            button.data('action', 'activate').removeClass('install').addClass('activate');
                        } else if (action === 'activate') {
                            button.replaceWith('<span class="tfhb-plugin-button plugin-status active">Activated</span>');
                        }
                    } else {
                        button.contents().first().replaceWith(originalText);
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    button.contents().first().replaceWith(originalText).removeClass('loading').prop('disabled', false);
                    loader.hide();
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    

})(jQuery);