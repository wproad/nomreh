<?php

namespace Nomreh\User;

use Nomreh\Core\Logger;
use Nomreh\Utilities\Helpers;

class Register {
    public function __construct() {
        add_action('wp_ajax_complete_registration', [$this, 'complete_registration_ajax_callback']);
        add_action('wp_ajax_nopriv_complete_registration', [$this, 'complete_registration_ajax_callback']);
    }
    /**
     * Register a new user with the given phone number.
     *
     * @param string $phone The phone number to register the user with.
     * @return \WP_User|\WP_Error The newly created user object or a WP_Error object on failure.
     */
    public static function register_user($phone,$first_name, $last_name, $email = null) {
        // Generate a fake email using the phone number and the website domain
        $domain = parse_url(home_url(), PHP_URL_HOST);
        $fake_email = 'u_' . $phone . '@' . $domain;

        // Generate a random password (you can change this to a fixed password if needed)
        $password = wp_generate_password();

        // Prepare user data
        $user_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . " " . $last_name,
            'user_login' => $phone,  // Use phone as the username
            'user_pass'  => $password,  // Set the password
            'user_email' => $email ?? $fake_email,  // Use the fake email
            'role'       => 'customer',  // Default role (you can change this if needed)
        );

        // Attempt to create the user
        $user_id = wp_insert_user($user_data);

        // Check for errors
        if (is_wp_error($user_id)) {
            return $user_id;  // Return the error object
        }

        // Add the phone number as user meta
        update_user_meta($user_id, 'phone', $phone);

        // Return the newly created user object
        return get_user_by('id', $user_id);
    }


    public function complete_registration_ajax_callback() {
        $phone = Helpers::fix_fa_nums($_POST['phone']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = !empty($_POST['email']) ? sanitize_email($_POST['email']) : null;

        $user = self::register_user($phone,$first_name, $last_name, $email);

        if (is_wp_error($user)) {
            wp_send_json_error(['message' => $user->get_error_message()]);
        }


        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID);

        (new Logger())->log_register_success($user->ID, $phone);

        wp_send_json_success(['message' => 'ثبت نام شما با موفقیت انجام شد.']);
    }
}