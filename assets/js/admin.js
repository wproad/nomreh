jQuery(document).ready(function($) {
    if ($.fn.wpColorPicker) {
        $('.nomreh-color-picker').wpColorPicker();
    }

    // Handle SMS provider selection
    $('#nomreh_sms_provider').on('change', function() {
        var selectedProvider = $(this).val();
        
        // Hide all provider-specific settings sections
        $('.provider-settings').hide();
        
        // Show the selected provider's settings
        if (selectedProvider) {
            $('.' + selectedProvider + '-settings').show();
        }
    });
    
    // Trigger change event on page load
    $('#nomreh_sms_provider').trigger('change');

    $('.nomreh-tools-form.ajax-form').on('submit', function(e) {
        e.preventDefault();

        var Form = $(this);
        var formBtn = Form.find('.spd-button');
        var formBtnText = formBtn.find('span').text();
        var resultLogEl = Form.find('.result-log');
        var formData = new FormData(this);

        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $.ajax({
            url: nomreh_obj.ajaxurl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 second timeout
            beforeSend: function() {
                formBtn.prop('disabled', true).addClass('loading')
                    .find('span').text('در حال پردازش ...');
            },
            success: function(response) {
                console.log(response)
                if (response.success) {
                    resultLogEl.prepend(response.data);
                } else {
                    resultLogEl.prepend(response.data);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = '';
                if (status === 'timeout') {
                    errorMessage = 'Request timed out';
                } else if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMessage = xhr.responseJSON.data;
                } else {
                    errorMessage = 'Ajax error: ' + error;
                }
                resultLogEl.prepend('<li class="error">' + errorMessage + '</li>');
            },
            complete: function() {
                formBtn.prop('disabled', false)
                    .removeClass('loading')
                    .find('span').text(formBtnText);
            }
        });
    });
});