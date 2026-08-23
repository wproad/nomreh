<?php

namespace Nomreh;

use Nomreh\Utilities\Helpers;

class Otp {

    // OTP expiration time in seconds (5 minutes)
    const OTP_EXPIRATION_TIME = 300;

    public function __construct() {
        // Hook the send_otp_code method to AJAX actions
        add_action('wp_ajax_send_otp_code', array($this, 'send_otp_code_ajax'));
        add_action('wp_ajax_nopriv_send_otp_code', array($this, 'send_otp_code_ajax')); // For non-logged in users
    }

    public static function generate_otp_code($phone) {
        // Generate a random 6-digit OTP code
        $otp_code = rand(1000, 9999);

        // Save the OTP code in the database
        self::save_otp_code($phone, $otp_code);

        return $otp_code;
    }

    private static function save_otp_code($phone, $otp_code) {
        global $wpdb;

        $table_name = $wpdb->prefix . NOMREH_LOGIN_CODE__TABLE_KEY;

        // Remove old OTPs for the phone number
        $wpdb->delete($table_name, ['phone' => $phone]);

        // Insert the new OTP code
        $wpdb->insert(
            $table_name,
            array(
                'phone' => $phone,
                'code' => $otp_code,
                'datetime' => current_time('mysql', 1) // UTC time
            )
        );
    }

    public static function send_otp_code_ajax() {

        // if captcha enabled add some checks
        $captcha_enabled = Captcha::is_captcha_enabled();
        if($captcha_enabled){
            if (!isset($_POST['captcha_code'])) {
                wp_send_json_error(['message' => 'کد امنیتی را وارد کنید']);
            }
            if (!is_numeric($_POST['captcha_code'])) {
                wp_send_json_error(['message' => 'برای کد امنیتی را یک مقدار عددی وارد کنید']);
            }

            $captcha_code = $_POST['captcha_code'];
            if(!Captcha::verify_captcha($captcha_code)){
                wp_send_json_error(['message' => 'کد امنیتی صحیح نیست!']);
            }
        }



        if (!isset($_POST['phone'])) {
            wp_send_json_error(['message' => 'شماره تلفن ضروری است']);
        }
        $phone = $_POST['phone'];

        // Convert Persian numbers to standard numbers
        $phone = Helpers::fix_fa_nums($phone);

        // Validate the phone number
        if (!Helpers::is_valid_phone($phone)) {
            wp_send_json_error(['message' => 'ساختار شماره موبایل وارد شده صحیح نیست!']);
        }

        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // Rate-limit only outside development (dev mode skips SMS + firewall).
        if (!NOMREH_DEVELOPMENT) {
            $firewall = new Firewall();
            $ip_check = $firewall->check_ip($user_ip);
            if (!$ip_check['success']) {
                wp_send_json_error(['message' => $ip_check['message']]);
            }
        }

        // Proceed to generate and send the OTP code
        $otp_code = self::generate_otp_code($phone);

        if (NOMREH_DEVELOPMENT) {
            wp_send_json_success(['message' => 'کد توسعه: ' . $otp_code]);
        }

        $firewall = new Firewall();
        $firewall->increment_attempts($user_ip);

        // Send the OTP code via SMS
        Sms::send_otp($otp_code, $phone);
    }

    public static function verify_otp_code($phone, $user_input_code) {
        global $wpdb;

        $table_name = $wpdb->prefix . NOMREH_LOGIN_CODE__TABLE_KEY;

        // Get the stored OTP code and datetime for the phone number
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT code, datetime FROM $table_name WHERE phone = %s",
            $phone
        ));

        if ($result) {
            // Check if the OTP code matches
            if ($result->code == $user_input_code) {
                // Check if the OTP is expired
                $otp_time = strtotime($result->datetime);
                $current_time = current_time('timestamp', 1); // UTC time

                if (($current_time - $otp_time) <= self::OTP_EXPIRATION_TIME) {
                    // OTP is valid and not expired
                    return ['success' => true ,  'message' => 'کد با موفقیت تایید شد.'];
                } else {
                    // OTP expired
                    return ['success' => false, 'message' => 'کد تایید منقضی شده است. صفحه را دوباره بارگزاری کنید.'];
                }
            } else {
                // OTP code does not match
                return ['success' => false, 'message' => 'کد وارد شده اشتباه است!'];
            }
        } else {
            // No OTP found for the phone number
            return ['success' => false, 'message' => 'کد تایید متعلق به این شماره نیست. دوباره تلاش کنید.'];
        }
    }

    public static function delete_otp_code($phone) {
        global $wpdb;

        $table_name = $wpdb->prefix . NOMREH_LOGIN_CODE__TABLE_KEY;

        $wpdb->delete($table_name, ['phone' => $phone]);
    }
}