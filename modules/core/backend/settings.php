<?php

if ( !class_exists( 'VPR_Settings' ) ) {
    class VPR_Settings {
        /**
         * @var array $options_keys The options keys used in this plugin
         */
        private $options_keys;

        /**
         * @var array VPR_Settings_Views the object that handles the settings html
         */
        public $settings_views;

        public function __construct() {
            $this->options_keys = (object) vpr_helpers()->get_meta_keys( true );
            $this->settings_views = new VPR_Settings_Views();
            $this->init();
        }

        /**
         * Adds the settings submenu
         */
        public function add_settings_submenu() {
            add_submenu_page(
                'options-general.php',
                __( 'Posts restrictions', 'wp-conditional-post-restrictions' ),
                __( 'Posts restrictions', 'wp-conditional-post-restrictions' ),
                'manage_options',
                'vpr_settings',
                array($this, 'render_settings')
            );
        }

        /**
         * Adds the posts restrictions settings section and registers that settings
         */
        public function add_settings() {
            //Adding settings
            register_setting( 'vpr_settings', 'vpr_conditions_enabled' );
            register_setting( 'vpr_settings', $this->options_keys->whitelisted_roles );
            register_setting( 'vpr_settings', $this->options_keys->what_happens_when_post_is_restricted );
            register_setting( 'vpr_settings', $this->options_keys->post_url_redirection );
            register_setting( 'vpr_settings', $this->options_keys->restricted_post_message );
            register_setting( 'vpr_settings', $this->options_keys->what_happens_when_category_is_restricted );
            register_setting( 'vpr_settings', $this->options_keys->error_message );
            register_setting( 'vpr_settings', $this->options_keys->category_url_redirection );
            register_setting( 'vpr_settings', $this->options_keys->show_the_content_of_another_page );
            register_setting( 'vpr_settings', 'vpr_hide_restricted_posts_from_menus' );
            add_settings_section(
                'vpr_settings_restrictions_section',
                __( 'Conditional Post Restrictions', 'wp-conditional-post-restrictions' ),
                array($this, 'display_restrictions_section_description'),
                'vpr_settings'
            );
            add_settings_field(
                'vpr_restrictions_instructions',
                __( 'Instructions', 'wp-conditional-post-restrictions' ),
                array($this->settings_views, 'instructions_view'),
                'vpr_settings',
                'vpr_settings_restrictions_section'
            );
            add_settings_field(
                'vpr_conditions_enabled',
                __( 'Enable restrictions', 'wp-conditional-post-restrictions' ),
                array($this, 'render_posts_restrictions_enabled_setting'),
                'vpr_settings',
                'vpr_settings_restrictions_section'
            );
            add_settings_field(
                $this->options_keys->whitelisted_roles,
                __( 'Don\'t apply restrictions for these user roles', 'wp-conditional-post-restrictions' ),
                array($this->settings_views, 'whitelisted_roles_field_view'),
                'vpr_settings',
                'vpr_settings_restrictions_section'
            );
            add_settings_field(
                $this->options_keys->what_happens_when_post_is_restricted,
                __( 'What happens when the post is restricted?', 'wp-conditional-post-restrictions' ),
                array($this->settings_views, 'what_happens_when_post_is_restricted_field_view'),
                'vpr_settings',
                'vpr_settings_restrictions_section'
            );
            add_settings_field(
                $this->options_keys->post_url_redirection,
                __( 'Redirect to this url', 'wp-conditional-post-restrictions' ),
                array($this->settings_views, 'post_url_redirection_field_view'),
                'vpr_settings',
                'vpr_settings_restrictions_section',
                array(
                    'class' => 'vpr_setting_field' . $this->get_option_hide_class( $this->options_keys->what_happens_when_post_is_restricted, 'redirect_to_url', 'redirect_to_url' ),
                )
            );
            add_settings_field(
                $this->options_keys->restricted_post_message,
                __( 'Error message', 'wp-conditional-post-restrictions' ),
                array($this->settings_views, 'restricted_post_message_field_view'),
                'vpr_settings',
                'vpr_settings_restrictions_section',
                array(
                    'class' => 'vpr_setting_field' . $this->get_option_hide_class( $this->options_keys->what_happens_when_post_is_restricted, 'redirect_to_url', array('remove_content_and_show_message', 'show_fragment_of_the_content_and_show_message_after_fragment') ),
                )
            );
            add_settings_field(
                'vpr_hide_restricted_posts_from_menus',
                __( 'Remove restricted posts from menus?', 'wp-conditional-post-restrictions' ),
                array($this->settings_views, 'render_remove_posts_from_menus_field'),
                'vpr_settings',
                'vpr_settings_restrictions_section'
            );
        }

        /**
         * Gets the css class to hide a setting html that depends of the value of another setting
         * 
         * @param string $option_name           The option name that changes other settings visibility depending of its value
         * @param mixed  $option_default_value  The default value of the option that changes other settings visibility
         * @param mixed  $expected_option_value The expected value to show the other setting 
         * 
         * @return string Returns ' vpr_hidden' if the option must be hidden, otherwise returns a empty string
         * 
         */
        public function get_option_hide_class( $option_name, $option_default_value, $expected_option_value ) {
            $option_value = get_option( $option_name, $option_default_value );
            if ( is_array( $expected_option_value ) ) {
                return ( !in_array( $option_value, $expected_option_value ) ? ' vpr-hidden' : '' );
            }
            return ( $option_value !== $expected_option_value ? ' vpr-hidden' : '' );
        }

        /**
         * Renders the checkbox to enable/disabled posts restrictions conditions
         */
        public function render_posts_restrictions_enabled_setting() {
            vpr_helpers()->conditions_handler()->enable_conditions_option_view();
        }

        /**
         * Renders the settings fields
         */
        public function render_settings() {
            $settings_html = $this->get_settings_html();
            vpr_helpers()->display_tag( array(
                'tag'        => 'form',
                'attributes' => array(
                    'method' => 'POST',
                    'action' => 'options.php',
                ),
                'content'    => $settings_html,
            ) );
        }

        /**
         * Gets the settings html as string
         */
        public function get_settings_html() {
            ob_start();
            settings_fields( 'vpr_settings' );
            do_settings_sections( 'vpr_settings' );
            submit_button();
            return ob_get_clean();
        }

        /**
         * Displays the settings description
         */
        public function display_restrictions_section_description() {
            do_action( 'wpcpr/settings_page/intro' );
        }

        /**
         * Enqueues the settings assets
         */
        public function enqueues( $hook ) {
            if ( empty( $_GET['page'] ) || $_GET['page'] !== 'vpr_settings' ) {
                return;
            }
            vpr_helpers()->conditions_handler()->enqueue_settings_assets();
            wp_enqueue_style( 'vpr_custom_settings', VPR_URL . 'assets/css/settings.css' );
            wp_enqueue_script( 'vpr_custom_settings', VPR_URL . 'assets/js/settings.js', array('jquery') );
        }

        /**
         * Initializes the settings actions and filters
         */
        public function init() {
            vpr_helpers()->conditions_handler()->add_delete_conditions_action();
            add_action( 'admin_menu', array($this, 'add_settings_submenu') );
            add_action( 'admin_init', array($this, 'add_settings') );
            add_action( 'admin_enqueue_scripts', array($this, 'enqueues') );
        }

    }

}