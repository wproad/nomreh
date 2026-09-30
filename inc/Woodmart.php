<?php
namespace Nomreh;

class Woodmart{

    private static $instance;

    public static function get_instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Whether the Woodmart sidebar replacement can run on this site.
     *
     * The option alone is not enough: the helpers used below ship with the
     * Woodmart theme, so a site that enables the option without the theme (or
     * without the header builder) would hit undefined functions.
     */
    public static function is_supported() {
        if(get_option('nomreh_woodmart_support') != 'yes') {
            return false;
        }

        return function_exists('woodmart_woocommerce_installed')
            && function_exists('whb_get_settings')
            && function_exists('whb_get_dropdowns_color')
            && function_exists('woodmart_enqueue_inline_style');
    }

    /**
     * Whether the sidebar login form is replaced by the Nomreh form on this
     * request. Consulted before the head is sent so Core\Assets knows whether
     * the pages need the public assets, and re-checked when the form renders so
     * the two can never disagree.
     */
    public static function is_sidebar_form_active() {
        if (!self::is_supported() || is_user_logged_in()) {
            return false;
        }

        if (!woodmart_woocommerce_installed() || is_account_page()) {
            return false;
        }

        $settings = whb_get_settings();

        return !empty($settings['account']['login_dropdown'])
            && isset($settings['account']['form_display'])
            && $settings['account']['form_display'] === 'side';
    }

    public function __construct(){
        if(self::is_supported()){
            // Remove the original sidebar login form
            add_action('init', function() {
                remove_action('woodmart_before_wp_footer', 'woodmart_sidebar_login_form', 160);
            });
            add_action('woodmart_before_wp_footer', [$this, 'sidebar_login_form'], 160);
        }

    }

    // Add this to your plugin file



// Add your custom sidebar login form

    public function sidebar_login_form() {
        if (!self::is_sidebar_form_active()) {
            return;
        }

        $wrapper_classes = '';

        if ('light' === whb_get_dropdowns_color()) {
            $wrapper_classes .= ' color-scheme-light';
        }

        $position = is_rtl() ? 'left' : 'right';
        $wrapper_classes .= ' wd-' . $position;

        woodmart_enqueue_inline_style('header-my-account-sidebar');
        woodmart_enqueue_inline_style('woo-mod-login-form');
        ?>
        <div class="login-form-side wd-side-hidden woocommerce<?php echo esc_attr($wrapper_classes); ?>">
            <div class="wd-heading">
                <span class="title"><?php esc_html_e('Sign in', 'woodmart'); ?></span>
                <div class="close-side-widget wd-action-btn wd-style-text wd-cross-icon">
                    <a href="#" rel="nofollow"><?php esc_html_e('Close', 'woodmart'); ?></a>
                </div>
            </div>

            <?php if (!is_checkout()) : ?>
                <?php woocommerce_output_all_notices(); ?>
            <?php endif; ?>

            <?php
            // Your custom shortcode here
            echo do_shortcode('[nomreh_otp_forms]');
            ?>
        </div>
        <?php
    }

}