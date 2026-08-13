<?php
namespace Nomreh\Core;

class Assets {

    private $plugin_version;

    private $plugin_name;

    private $plugin_url;

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
     */
    public function load_public_assets() {
        wp_enqueue_style($this->plugin_name . '-toastify', $this->plugin_url . '/assets/css/toastify.css' , array(), $this->plugin_version, 'all');
        wp_enqueue_style($this->plugin_name, $this->plugin_url . '/assets/css/public.css' , array(), $this->plugin_version, 'all');

        $brand_css = $this->get_brand_inline_css();
        if ($brand_css !== '') {
            wp_add_inline_style($this->plugin_name, $brand_css);
        }

        wp_enqueue_script($this->plugin_name . '-toastify', $this->plugin_url . '/assets/js/toastify.js' , array('jquery'), $this->plugin_version, false);
        wp_enqueue_script($this->plugin_name, $this->plugin_url . '/assets/js/public.js' , array('jquery'), $this->plugin_version, false);


        wp_localize_script($this->plugin_name, 'nomreh_pub_obj', array(
            'ajax_nonce' => wp_create_nonce('nomreh_ajax_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
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
            $admin_deps[] = 'wp-color-picker';
        }

        wp_enqueue_script($this->plugin_name, $this->plugin_url . '/assets/js/admin.js' , $admin_deps, $this->plugin_version, false);


        wp_localize_script($this->plugin_name, 'nomreh_obj', array(
            'ajax_nonce' => wp_create_nonce('nomreh_ajax_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));

    }

    /**
     * Build inline CSS that overrides frontend brand color variables.
     */
    private function get_brand_inline_css() {
        $first = sanitize_hex_color(get_option('nomreh_first_color', ''));
        $alt   = sanitize_hex_color(get_option('nomreh_first_color_alt', ''));

        if (!$first && !$alt) {
            return '';
        }

        $css = ':root{';
        if ($first) {
            $css .= '--first-color:' . $first . ';';
        }
        if ($alt) {
            $css .= '--first-color-alt:' . $alt . ';';
        }
        $css .= '}';

        return $css;
    }
}
