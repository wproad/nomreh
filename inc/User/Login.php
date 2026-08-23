<?php

namespace Nomreh\User;

use Nomreh\Permissions;
use Nomreh\Nomreh;
use Nomreh\Core\Logger;
use Nomreh\Utilities\Helpers;

class Login {

    public function __construct() {
        add_action('wp_ajax_user_login', array($this, 'user_login_callback'));
        add_action('wp_ajax_nopriv_user_login', array($this, 'user_login_callback')); // For non-logged in users
    }

    public function user_login_callback() {
        // Sanitize and validate the phone number and OTP code from the request
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $otp_code = isset($_POST['otp_code']) ? sanitize_text_field($_POST['otp_code']) : '';

        // Convert Persian numbers to standard numbers
        $phone = Helpers::fix_fa_nums($phone);
        $otp_code = Helpers::fix_fa_nums($otp_code);

        // Ensure both the phone number and OTP code are provided
        if (empty($phone) || empty($otp_code)) {
            wp_send_json_error(['message' => 'اطلاعات فرم به درستی وارد نشده است.']);
        }

        // Verify the OTP code
        $otp_verification = \Nomreh\Otp::verify_otp_code($phone, $otp_code);

        if ($otp_verification['success']) {
            // OTP code verified, attempt to log in the user

            // Only check Nomreh's own user fields
            $user = User::user_exist($phone);

            // If no user was found, tell js to show register form
            if (!$user) {
                wp_send_json_success([
                    'message' => 'شما کاربر جدید هستید. ثبت نام خود را تکمیل کنید.',
                    'is_new_user' => true
                ]);
                return;
            }


            // check permissions for login
            $permissions = new Permissions();
            $check_role = $permissions->check_user_role($user->ID);
            if(!$check_role['success']){
                wp_send_json_error(['message' => $check_role['message']]);
            }


            // If a user was found or successfully registered, log them in
            update_user_meta($user->ID, 'phone', $phone);
            wp_set_current_user($user->ID, $user->user_login);
            wp_set_auth_cookie($user->ID);

            // Delete the OTP code after successful login
            \Nomreh\Otp::delete_otp_code($phone);

            (new Logger())->log_login_success($user->ID, $phone);

            // Send success response
            wp_send_json_success(['message' => 'با موفقیت وارد شدید.']);
        } else {
            // Send error if OTP verification failed
            wp_send_json_error(['message' => $otp_verification['message']]);
        }
    }
}