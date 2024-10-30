/**
* All of the JavaScript for your admin-specific functionality 
* should be included in this file.
*/

(function ($) {
    $('#smokedrop-check-update').on('click', function (event) {
        event.preventDefault();

        // Set the variable for status message.
        var message = $('.smokedrop-update-message');
        var spinner = $('.smokedrop-update-button .spinner');

        // Show the spinner
        spinner.addClass('is-active');

        // Send AJAX request to API.
        $.ajax({
            method: 'POST',
            url: smokedrop_ajax.ajax_url,
            data: {
                action: 'smokedrop_check_update',
                _ajax_nonce: smokedrop_ajax.nonce
            }
        }).done(function (response) {
            setTimeout(function () {
                if (response.success) {
                    console.log(response.data?.message);
                    message.html('<p style="color: green;">' + response.data?.message + '</p>');
                } else {
                    console.warn(response.data?.message);
                    message.html('<p style="color: red;">' + response.data?.message + '</p>');
                }
                spinner.removeClass('is-active');
            }, 2000);
        }).fail(function () {
            setTimeout(function () {
                message.html('<p style="color: red;">Something went wrong!</p>');
                console.error('Something went wrong!');
                spinner.removeClass('is-active');
            }, 2000);
        });
    });
})(jQuery);