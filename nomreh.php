<?php
/*
Plugin Name: نُمره
Plugin URI: https://nomreh-landing.vercel.app/
Description: افزونه لاگین و ثبت نام با کد تایید پیامکی برای وردپرس. پشتیبانی از ملی پیامک و کاوه نگار. مناسب برای سایت‌های فارسی و ووکامرس.
Version: 0.11.3
Requires at least: 6.0.0
Tested up to: 7.1.0
Requires PHP: 7.4.0
Author: WP Road
Author URI: https://github.com/wproad
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: nomreh
Domain Path: /languages
Network: false
*/

namespace Nomreh;

if (!defined('ABSPATH')) {
    exit;
}

class Nomreh{

    public static $plugin_url;
    public static $plugin_path;
    public static $plugin_version;

    public static $plugin_text_domain;

    protected static $_instance = null;

    public static function instance()
    {
        null === self::$_instance and self::$_instance = new self;
        return self::$_instance;
    }

    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->first_checks();
        $this->hooks();
        $this->instances();
    }

    private function first_checks(){
        $this->check_and_update_cron();
        $this->plugin_update_check();
        $this->check_and_update_db();
    }
    private function define_constants(){
        /*
         * Get Plugin Data
         */
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data(__FILE__);

        self::$plugin_version = $plugin_data['Version'];
        self::$plugin_text_domain = $plugin_data['TextDomain'];

        self::$plugin_url = plugins_url('', __FILE__);

        self::$plugin_path = plugin_dir_path(__FILE__);


        /**
         * Define needed constants to use in plugin
         */
        define('NOMREH_PLUGIN_TEXT_DOMAIN', self::$plugin_text_domain);
        define('NOMREH_PLUGIN_VERSION', self::$plugin_version);
        define('NOMREH_PLUGIN_PATH', self::$plugin_path);
        define('NOMREH_PLUGIN_URL', self::$plugin_url);
        define('NOMREH_DB_VERSION', '1.3');
        define('NOMREH_CRON_VERSION', '1.1');
        define('NOMREH_DEVELOPMENT', false);

        define('NOMREH_LOGIN_CODE__TABLE_KEY', 'nomreh_login_code');
        define('NOMREH_LOGIN_IP__TABLE_KEY', 'nomreh_login_ip');
        define('NOMREH_LOGIN_VERSION__OPT_KEY', '_nomreh_login_version');
        define('NOMREH_LOGIN_CRON_VERSION__OPT_KEY', '_nomreh_login_cron_version');
        define('NOMREH_LOGIN_DB_VERSION__OPT_KEY', '_nomreh_login_db_version');


        // @todo get these data from option page
        define('NOMREH_REDIRECT_URL', get_site_url());
        define('NOMREH_LOGIN_PAGE_SLUG', 'my-account');


    }

    public function hooks(){
        add_action('init', function() {
            $installer = new Core\Install();
            register_activation_hook(__FILE__, [$installer, 'run_install']);

            $uninstaller = new Core\Uninstall();
            register_deactivation_hook(__FILE__, [$uninstaller, 'run_uninstall']);
        });
    }

    public function includes(){
        include dirname(__FILE__) . '/vendor/autoload.php';
        include dirname(__FILE__) . '/inc/Utilities/Functions.php';
        /**
         * Plugin update checker library
         * Source : https://github.com/YahnisElsts/plugin-update-checker
         */
        require_once dirname(__FILE__) . '/inc/puc/plugin-update-checker.php';
    }

    public function instances(){
        new Core\Assets();
        new CronJobs();
        new User\Login();
        new User\Register();
        new User\UsersList();
        User\User::get_instance();
        new Menu();
        new Tools();
        new FormShortcodes();
        new Otp();
        Woodmart::get_instance();

        // Initialize SMS system
        Sms::init();

        add_action('plugins_loaded', function() {
            if ( class_exists( 'WooCommerce' ) ) {
                new Woocommerce();
            }
        });


    }

    public function check_and_update_cron() {
        $installed_cron_version = get_option(NOMREH_LOGIN_CRON_VERSION__OPT_KEY);

        if (!$installed_cron_version || $installed_cron_version !== NOMREH_CRON_VERSION) {
            $cron_jobs = new CronJobs();
            $cron_jobs->reschedule_events('plugin cronjob version changed');

            update_option(NOMREH_LOGIN_CRON_VERSION__OPT_KEY, NOMREH_CRON_VERSION);
        }
    }

    public function plugin_update_check()
    {
        $update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/wproad/nomreh',
            __FILE__,
            'nomreh'
        );
        //Set the branch that contains the stable release.
        $update_checker->setBranch('main');
    }

    public function check_and_update_db() {
        $installed_version = get_option(NOMREH_LOGIN_DB_VERSION__OPT_KEY, );

        global $wpdb;
        $code_table_name =   $wpdb->prefix . NOMREH_LOGIN_CODE__TABLE_KEY;
        $ip_table_name =     $wpdb->prefix . NOMREH_LOGIN_IP__TABLE_KEY;

        if (!$installed_version || $installed_version !== NOMREH_DB_VERSION
            || $wpdb->get_var("SHOW TABLES LIKE '$code_table_name'") != $code_table_name
            || $wpdb->get_var("SHOW TABLES LIKE '$ip_table_name'") != $ip_table_name
        ) {
            $db = new Core\Db();
            $db->make_tables();
        }
    }

}

function run_nomreh(){
    return Nomreh::instance();
}

run_nomreh();

// @todo
//          - check for none woocommerce site compatibility
//          - options : to select which roles can login via otp
//          - options : for firewall variables
//          - fix : reset loading after error message
//          - options : error and success permission messages
//          - style : https://www.mobit.ir/auth
//          - feat : cronjob to empty tables
//          - feat : index for ip tables
//          - feat : firewall ui, manually block ip or phone
//          - feat : notify me when product was in-stock
//          - feat : abondoned cart
//          - feat : make daily reports and send to admin
