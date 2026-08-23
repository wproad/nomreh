function spd_toast (message, type = "error") {
    if(message != null){
        var toastClass = "spd-toast-info";
        if (type === "error") {
            toastClass = "spd-toast-error";
        } else if (type === "success") {
            toastClass = "spd-toast-success";
        }

        var isRtl = !!(typeof nomreh_pub_obj !== "undefined" && nomreh_pub_obj.is_rtl);
        Toastify({
            text: message,
            duration: 5000,
            newWindow: true,
            close: true,
            className: toastClass,
            gravity: "bottom", // `top` or `bottom`
            position: isRtl ? "right" : "left",
            stopOnFocus: true, // Prevents dismissing of toast on hover
        }).showToast();
    }
}

jQuery(document).ready(function($) {
    var resendCountdown = null;
    var $otpDigits = $('.spd-otp-digits .otp-digit');

    function clearResendTimer() {
        if (resendCountdown) {
            clearInterval(resendCountdown);
            resendCountdown = null;
        }
        var $display = $('#resend-otp');
        $display.hide();
        $display.find('.timer').text('').hide();
        $display.find('.resend-btn').hide();
    }

    function toEnglishDigits(value) {
        return String(value || '')
            .replace(/[۰-۹]/g, function(d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
            .replace(/[٠-٩]/g, function(d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
    }

    function getOtpCode() {
        var code = '';
        $otpDigits.each(function() {
            code += toEnglishDigits($(this).val()).replace(/\D/g, '');
        });
        return code;
    }

    function syncOtpCode() {
        $('#otp_code').val(getOtpCode());
    }

    function clearOtpDigits(focusFirst) {
        $otpDigits.val('');
        syncOtpCode();
        if (focusFirst) {
            $otpDigits.first().trigger('focus');
        }
    }

    function fillOtpDigits(code) {
        var digits = toEnglishDigits(code).replace(/\D/g, '').slice(0, $otpDigits.length).split('');
        $otpDigits.each(function(index) {
            $(this).val(digits[index] || '');
        });
        syncOtpCode();
        if (digits.length >= $otpDigits.length) {
            $otpDigits.last().trigger('focus');
        } else if (digits.length > 0) {
            $otpDigits.eq(digits.length).trigger('focus');
        }
    }

    $otpDigits.on('input', function() {
        var $input = $(this);
        var value = toEnglishDigits($input.val()).replace(/\D/g, '');

        if (value.length > 1) {
            fillOtpDigits(value);
            return;
        }

        $input.val(value);
        syncOtpCode();

        if (value && $input.index() < $otpDigits.length - 1) {
            $otpDigits.eq($input.index() + 1).trigger('focus');
        }
    });

    $otpDigits.on('keydown', function(e) {
        var $input = $(this);
        var index = $input.index();

        if (e.key === 'Backspace' && !$input.val() && index > 0) {
            $otpDigits.eq(index - 1).val('').trigger('focus');
            syncOtpCode();
            e.preventDefault();
        } else if (e.key === 'ArrowLeft' && index > 0) {
            $otpDigits.eq(index - 1).trigger('focus');
            e.preventDefault();
        } else if (e.key === 'ArrowRight' && index < $otpDigits.length - 1) {
            $otpDigits.eq(index + 1).trigger('focus');
            e.preventDefault();
        }
    });

    $otpDigits.on('paste', function(e) {
        e.preventDefault();
        var pasted = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        fillOtpDigits(pasted);
    });

    $otpDigits.on('focus', function() {
        $(this).select();
    });

    // Handle Send OTP form submission
    $('#send-otp-form').on('submit', function(e) {
        e.preventDefault();
        var Form = $(this),
            submitBtn = Form.find('.spd-button'),
            messageEl = Form.find('.form-message')

        var phone = $(this).find('#phone').val();
        var captcha_code = $(this).find('#captcha-input').val();
        var timerDuration = 60; // Countdown timer duration in seconds

        $.ajax({
            url: nomreh_pub_obj.ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'send_otp_code',
                phone: phone,
                captcha_code: captcha_code,
            },
            beforeSend: function(){
                submitBtn.addClass('loading')
            },
            success: function(response) {
                if (response.success) {
                    spd_toast(response.data.message, 'success')
                    $('#phone-clone').text(phone)
                    // Hide the Send OTP form and show the Verify OTP form
                    $('#send-otp-form').hide();
                    $('#verify-otp-form').show();
                    clearOtpDigits(true);
                    startTimer(timerDuration); // Start the countdown timer
                } else {
                    spd_toast(response.data.message)
                    refreshCaptcha()
                }
            },
            error: function(e) {
                console.log(e);
                spd_toast('مشکل ارتباط با سرور')
            },
            complete: function(){
                submitBtn.removeClass('loading')
            }
        });
    });

    $('#spd-captcha-image').on('click', function() {
        refreshCaptcha()
    });

    function refreshCaptcha(){
        var captchaImage = $('#spd-captcha-image')
        if (!captchaImage.length) {
            return;
        }
        // Clear the input field
        $('#captcha-input').val('');
        // Generate a random number to append to the URL
        var randomNumber = Math.floor(Math.random() * 1000);
        // Get the current src attribute of the captcha image
        var currentSrc = captchaImage.attr('src');
        // Check if the src contains a query string
        var separator = currentSrc.indexOf('?') !== -1 ? '&' : '?';
        // Construct the new src with the random number
        var newSrc = currentSrc + separator + 'rand=' + randomNumber;
        // Update the src attribute of the captcha image with the new URL
        captchaImage.attr('src', newSrc);
    }

    $('#change-phone').on('click', function(e) {
        e.preventDefault();
        clearResendTimer();
        clearOtpDigits(false);
        $('#verify-otp-form').hide();
        $('#send-otp-form').show();
        refreshCaptcha();
        $('#phone').trigger('focus');
    });

    $('#resend-otp').on('click', '.resend-btn', function(e){
        e.preventDefault()
        $('#send-otp-form').submit()
    })

    function startTimer(duration) {
        var timer = duration, minutes, seconds;
        var displayEl = $('#resend-otp'),
            timerEl = displayEl.find('.timer'),
            resendBtnEl = displayEl.find('.resend-btn')

        clearResendTimer();

        displayEl.show(0); // Show the timer
        timerEl.show(0);
        resendBtnEl.hide(0)

        resendCountdown = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            timerEl.html( minutes + ":" + seconds);

            if (--timer < 0) {
                clearInterval(resendCountdown);
                resendCountdown = null;
                timerEl.hide(0)
                resendBtnEl.show(0)
            }
        }, 1000);
    }

    // Handle Verify OTP form submission
    $('#verify-otp-form').on('submit', function(e) {
        e.preventDefault();
        var Form = $(this),
            submitBtn = Form.find('.spd-button'),
            messageEl = Form.find('.form-message')

        var phone = $('#phone').val();
        var otp_code = getOtpCode();
        var redirect_url = $(this).find('#redirect_url').val();

        if (otp_code.length !== 4) {
            spd_toast('لطفا کد ۴ رقمی را کامل وارد کنید');
            $otpDigits.first().trigger('focus');
            return;
        }

        $('#otp_code').val(otp_code);

        $.ajax({
            url: nomreh_pub_obj.ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'user_login', // AJAX action (user_login)
                phone: phone,
                otp_code: otp_code,
            },
            beforeSend: function(){
                submitBtn.addClass('loading')
            },
            success: function(response) {
                if (response.success) {
                    spd_toast(response.data.message, 'success')

                    // Check for new user registration
                    if (response.data.is_new_user) {
                        $('#verify-otp-form').hide();
                        $('#reg_phone').val(phone);
                        $('#reg_redirect_url').val(redirect_url);
                        $('#complete-registration-form').show();
                    } else {
                        setTimeout(function(){
                            window.location.href = redirect_url;
                        }, 1000);
                    }
                } else {
                    spd_toast(response.data.message)
                    clearOtpDigits(true);
                }
            },
            error: function() {
                spd_toast('مشکل ارتباط با سرور.')
            },
            complete: function(){
                submitBtn.removeClass('loading')
            }
        });
    });

    $('#complete-registration-form').on('submit', function(e) {
        e.preventDefault();
        var Form = $(this),
            submitBtn = Form.find('.spd-button'),
            messageEl = Form.find('.form-message')

        $.ajax({
            url: nomreh_pub_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'complete_registration',
                phone: $('#reg_phone').val(),
                first_name: $('#first_name').val(),
                last_name: $('#last_name').val(),
                email: $('#email').val(),
            },
            beforeSend: function(){
                submitBtn.addClass('loading')
            },
            success: function(response) {
                if (response.success) {
                    spd_toast(response.data.message, 'success')
                    setTimeout(function(){
                        window.location.href = $('#reg_redirect_url').val();
                    }, 1000);
                } else {
                    spd_toast(response.data.message)
                }
            },
            error: function(xhr){
                spd_toast('مشکل ارتباط با سرور')
                console.log(xhr);
            },
            complete: function(){
                submitBtn.removeClass('loading')
            }
        });
    });
});