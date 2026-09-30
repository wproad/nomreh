<?php
namespace Nomreh\Core;

use Nomreh\FormShortcodes;
use Nomreh\Woodmart;

class Assets {

    private static $instance;

    /**
     * Set by the shortcode while it renders, for the pages that the head-time
     * check in form_is_expected() cannot see.
     *
     * @var bool
     */
    private static $form_rendered = false;

    private $plugin_version;

    private $plugin_name;

    private $plugin_url;

    /**
     * Guards the inline <style> blocks against being printed twice, since the
     * shortcode can ask for the assets after the head check already enqueued
     * them.
     */
    private $public_assets_enqueued = false;

    public static function get_instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __construct() {
        $this->plugin_name = NOMREH_PLUGIN_TEXT_DOMAIN;
        $this->plugin_version = NOMREH_PLUGIN_VERSION;
        $this->plugin_url = NOMREH_PLUGIN_URL;



        if(!is_admin())
            add_action('wp_enqueue_scripts', [$this, 'load_public_assets']);


        if(is_admin())
            add_action('admin_enqueue_scripts', [$this, 'load_admin_assets']);
    }

    /**
     * Load public-facing assets
     *
     * Nothing on the front end uses these files except the login/register form,
     * so they are only enqueued when the form is going to be rendered. This
     * keeps them out of every other page view, and the `wp_footer` fallback
     * below covers the requests that are only discovered while rendering.
     */
    public function load_public_assets() {
        if (!$this->form_is_expected()) {
            // The form can also be pulled in by a widget, a block or a theme
            // template, none of which are visible to the checks above. Those
            // cases are discovered while rendering, which is after the head has
            // been sent, so serve the assets in the footer for them instead.
            add_action('wp_footer', [$this, 'enqueue_public_assets_if_rendered'], 1);
            return;
        }

        $this->enqueue_public_assets();
    }

    /**
     * Enqueue the public assets once the shortcode has actually rendered.
     */
    public function enqueue_public_assets_if_rendered() {
        if (self::$form_rendered) {
            $this->enqueue_public_assets();
        }
    }

    /**
     * Record that the form markup was rendered on this request.
     */
    public static function mark_form_rendered() {
        self::$form_rendered = true;
    }

    /**
     * Whether the login/register form is expected on the current request.
     *
     * Runs on `wp_enqueue_scripts`, i.e. before the head is sent, so the
     * stylesheets still land in the head rather than in the footer.
     */
    private function form_is_expected() {
        // The shortcode redirects logged in visitors instead of rendering the
        // form, so a guest is the only case that needs the assets at all.
        if (is_user_logged_in()) {
            return false;
        }

        // WooCommerce swaps the account page content for the form.
        if (function_exists('is_account_page') && is_account_page()) {
            return true;
        }

        // The shortcode placed in the content of the page being viewed.
        $queried_object = get_queried_object();
        if ($queried_object instanceof \WP_Post
            && has_shortcode($queried_object->post_content, FormShortcodes::SHORTCODE)
        ) {
            return true;
        }

        // The Woodmart sidebar prints the form in the footer of every page.
        return Woodmart::is_sidebar_form_active();
    }

    /**
     * Enqueue the public-facing assets. Safe to call more than once.
     */
    public function enqueue_public_assets() {
        if ($this->public_assets_enqueued) {
            return;
        }
        $this->public_assets_enqueued = true;

        $css_ver = $this->asset_version('/assets/css/public.css');
        $toastify_css_ver = $this->asset_version('/assets/css/toastify.css');
        $js_ver = $this->asset_version('/assets/js/public.js');
        $toastify_js_ver = $this->asset_version('/assets/js/toastify.js');

        wp_enqueue_style($this->plugin_name . '-toastify', $this->plugin_url . '/assets/css/toastify.css' , array(), $toastify_css_ver, 'all');
        wp_enqueue_style($this->plugin_name, $this->plugin_url . '/assets/css/public.css' , array(), $css_ver, 'all');

        $style_css = $this->get_style_inline_css();
        if ($style_css !== '') {
            wp_add_inline_style($this->plugin_name, $style_css);
        }

        $custom_styles = get_option('nomreh_custom_styles', '');
        if (is_string($custom_styles) && $custom_styles !== '') {
            wp_add_inline_style($this->plugin_name, $custom_styles);
        }

        wp_enqueue_script($this->plugin_name . '-toastify', $this->plugin_url . '/assets/js/toastify.js' , array(), $toastify_js_ver, false);
        $this->defer_script($this->plugin_name . '-toastify');

        wp_enqueue_script($this->plugin_name, $this->plugin_url . '/assets/js/public.js' , array('jquery'), $js_ver, false);
        $this->defer_script($this->plugin_name);


        wp_localize_script($this->plugin_name, 'nomreh_pub_obj', array(
            'ajax_nonce' => wp_create_nonce('nomreh_ajax_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'is_rtl' => is_rtl(),
        ));

    }

    /**
     * Load admin-facing assets
     */
    public function load_admin_assets($hook) {
        wp_enqueue_style($this->plugin_name, $this->plugin_url . '/assets/css/admin.css' , array(), $this->plugin_version, 'all');

        $admin_deps = array('jquery');
        if ($hook === 'settings_page_nomreh') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_editor();
            $admin_deps[] = 'wp-color-picker';
        }

        wp_enqueue_script($this->plugin_name, $this->plugin_url . '/assets/js/admin.js' , $admin_deps, $this->plugin_version, false);


        wp_localize_script($this->plugin_name, 'nomreh_obj', array(
            'ajax_nonce' => wp_create_nonce('nomreh_ajax_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));

    }

    /**
     * Keep a script off the render-blocking path.
     *
     * Uses the core `strategy` data, which is honoured from WP 6.3 onwards. On
     * older versions the value is simply stored and ignored, so the script
     * keeps its previous behaviour rather than breaking.
     */
    private function defer_script($handle) {
        wp_script_add_data($handle, 'strategy', 'defer');
    }

    /**
     * Prefer filemtime so CSS/JS edits bust browser cache without a version bump.
     */
    private function asset_version($relative_path) {
        $path = NOMREH_PLUGIN_PATH . ltrim($relative_path, '/');
        if (is_readable($path)) {
            return (string) filemtime($path);
        }
        return $this->plugin_version;
    }

    /**
     * Build inline CSS that overrides frontend style tokens from Settings → Styles.
     */
    private function get_style_inline_css() {
        $first       = sanitize_hex_color(get_option('nomreh_first_color', ''));
        $alt         = sanitize_hex_color(get_option('nomreh_first_color_alt', ''));
        $surface     = sanitize_hex_color(get_option('nomreh_surface_color', ''));
        $border      = sanitize_hex_color(get_option('nomreh_border_color', ''));
        $button_text = sanitize_hex_color(get_option('nomreh_button_text_color', ''));
        $radius_raw  = get_option('nomreh_radius', '');
        $radius      = (is_string($radius_raw) || is_numeric($radius_raw)) && preg_match('/^\d+(\.\d+)?$/', (string) $radius_raw)
            ? (string) $radius_raw . 'px'
            : '';

        if (!$first && !$alt && !$surface && !$border && !$button_text && $radius === '') {
            return '';
        }

        $css = ':root{';
        if ($first) {
            $css .= '--first-color:' . $first . ';';
        }
        if ($alt) {
            $css .= '--first-color-alt:' . $alt . ';';
        }
        if ($surface) {
            $css .= '--spd-surface:' . $surface . ';';
        }
        if ($border) {
            $css .= '--spd-border-color:' . $border . ';';
        }
        if ($button_text) {
            $css .= '--spd-button-text:' . $button_text . ';';
        }
        if ($radius !== '') {
            $css .= '--spd-radius:' . $radius . ';';
        }
        $css .= '}';

        return $css;
    }
}
