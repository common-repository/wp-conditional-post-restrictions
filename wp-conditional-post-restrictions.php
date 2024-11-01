<?php

/*
 Plugin Name: WP Conditional Post Restrictions
 Description: Restricts posts based on different conditions
 Version: 1.2.4
 Author: WP Super Admins
 Author URI: https://wpsuperadmins.com/plugins/wp-conditional-post-restrictions/?utm_source=wp-admin&utm_campaign=plugins-list&utm_medium=web&utm_term=author-link
 Plugin URI: https://wpsuperadmins.com/plugins/wp-conditional-post-restrictions/?utm_source=wp-admin&utm_campaign=plugins-list&utm_medium=plugin-link
    Text Domain: wp-conditional-post-restrictions
 Domain Path: /lang
*/
if ( !defined( 'WPCPR_MAIN_FILE' ) ) {
    define( 'WPCPR_MAIN_FILE', __FILE__ );
}
if ( !defined( 'WPCPR_DIST_DIR' ) ) {
    define( 'WPCPR_DIST_DIR', __DIR__ );
}
require_once WPCPR_DIST_DIR . '/vendor/vg-plugin-sdk/index.php';
require_once WPCPR_DIST_DIR . '/inc/freemius-init.php';
if ( !class_exists( 'WP_Conditional_Post_Restrictions_Dist' ) ) {
    class WP_Conditional_Post_Restrictions_Dist {
        private static $instance = false;

        static $dir = __DIR__;

        static $version = '1.2.4';

        static $name = 'Conditional Post Restrictions';

        var $args = null;

        var $vg_plugin_sdk = null;

        private function __construct() {
        }

        /**
         * Creates or returns an instance of this class.
         */
        static function get_instance() {
            if ( null == self::$instance ) {
                self::$instance = new WP_Conditional_Post_Restrictions_Dist();
                self::$instance->init();
            }
            return self::$instance;
        }

        function init() {
            $this->args = array(
                'main_plugin_file'           => __FILE__,
                'show_welcome_page'          => true,
                'welcome_page_url'           => admin_url( 'options-general.php?page=vpr_settings' ),
                'welcome_page_file'          => self::$dir . '/views/welcome-page-content.php',
                'plugin_name'                => self::$name,
                'plugin_prefix'              => 'wpcpr_',
                'plugin_version'             => self::$version,
                'plugin_options'             => get_option( 'vc_wc_cr_variations_per_country_tab_product_select_country_setting', false ),
                'default_billing_period'     => WP_FS__PERIOD_ANNUALLY,
                'buy_url'                    => wpcpr_fs()->checkout_url( WP_FS__PERIOD_ANNUALLY, true ),
                'buy_text'                   => __( 'Try Premium Plugin for FREE - 7 Days', 'wp-conditional-post-restrictions' ),
                'can_use_premium_code'       => wpcpr_fs()->can_use_premium_code__premium_only(),
                'free_offer_expiration_date' => '2020-11-01',
            );
            if ( !empty( $this->args['free_offer_expiration_date'] ) && date( 'Y-m-d' ) < $this->args['free_offer_expiration_date'] ) {
                $this->args['buy_text'] = __( 'Use Premium Plugin for FREE for 1 YEAR', 'wp-conditional-post-restrictions' );
                $this->args['buy_url'] = add_query_arg( 'coupon', 'FREE8987', $this->args['buy_url'] );
            }
            $this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK($this->args);
            $modules = $this->get_modules_list();
            if ( empty( $modules ) ) {
                return;
            }
            // Load all modules
            foreach ( $modules as $module ) {
                $path = ( file_exists( __DIR__ . "/modules/{$module}/{$module}.php" ) ? __DIR__ . "/modules/{$module}/{$module}.php" : __DIR__ . "/modules/{$module}/index.php" );
                if ( file_exists( $path ) ) {
                    require $path;
                }
            }
            add_action( 'plugins_loaded', array($this, 'late_init') );
            add_action( 'init', array($this, 'on_init') );
            // Disable WC's marketplace ads
            add_filter( 'woocommerce_allow_marketplace_suggestions', '__return_false' );
        }

        function on_init() {
            load_plugin_textdomain( 'wp-conditional-post-restrictions', false, basename( dirname( __FILE__ ) ) . '/lang/' );
        }

        function late_init() {
            $inc_files = array_merge( glob( __DIR__ . '/inc/*' ), glob( __DIR__ . '/integrations/*' ) );
            if ( is_admin() ) {
                $inc_files = array_merge( $inc_files, glob( __DIR__ . '/backend/*' ) );
            }
            foreach ( $inc_files as $inc_file ) {
                if ( !is_file( $inc_file ) ) {
                    continue;
                }
                require_once $inc_file;
            }
            load_plugin_textdomain( 'wp-conditional-post-restrictions', false, basename( dirname( __FILE__ ) ) . '/languages' );
        }

        /**
         * Get all modules in the folder
         * @return array
         */
        function get_modules_list() {
            $directories = glob( __DIR__ . '/modules/*', GLOB_ONLYDIR );
            if ( !empty( $directories ) ) {
                $directories = array_map( 'basename', $directories );
            }
            return $directories;
        }

        function __set( $name, $value ) {
            $this->{$name} = $value;
        }

        function __get( $name ) {
            return $this->{$name};
        }

    }

}
if ( !function_exists( 'WPCPR' ) ) {
    function WPCPR() {
        return WP_Conditional_Post_Restrictions_Dist::get_instance();
    }

}
WPCPR();