<?php

if ( !class_exists( 'VPR_Settings_Views' ) ) {
    class VPR_Settings_Views {
        private $options_keys;

        public function __construct() {
            $this->options_keys = (object) vpr_helpers()->get_meta_keys( true );
        }

        public function whitelisted_roles_field_view() {
            if ( !function_exists( 'get_editable_roles' ) ) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }
            $roles = wp_list_pluck( get_editable_roles(), 'name' );
            $roles['guest'] = __( 'Guest user', 'wp-conditional-post-restrictions' );
            vpr_helpers()->select( array(
                'id'                  => $this->options_keys->whitelisted_roles,
                'name'                => $this->options_keys->whitelisted_roles . '[]',
                'options'             => $roles,
                'wrapped'             => false,
                'multiple'            => 'multiple',
                'data-options_fields' => htmlspecialchars( json_encode( wp_list_pluck( get_editable_roles(), 'name' ) ) ),
                'value'               => get_option( $this->options_keys->whitelisted_roles ),
            ) );
            ?>
			<p><?php 
            _e( 'By default, we will not apply restrictions for administrators, editors, and any user with the capability edit_others_posts.', 'wp-conditional-post-restrictions' );
            ?></p>
			<?php 
        }

        public function what_happens_when_post_is_restricted_field_view() {
            vpr_helpers()->select( array(
                'id'                  => $this->options_keys->what_happens_when_post_is_restricted,
                'name'                => $this->options_keys->what_happens_when_post_is_restricted,
                'options'             => vpr_helpers()->get_field_options( '_' . $this->options_keys->what_happens_when_post_is_restricted ),
                'wrapped'             => false,
                'data-options_fields' => htmlspecialchars( json_encode( array(
                    'redirect_to_url'                                              => 'vpr_post_url_redirection',
                    'remove_content_and_show_message'                              => 'wp-vpr_restricted_post_message-wrap',
                    'show_fragment_of_the_content_and_show_message_after_fragment' => 'wp-vpr_restricted_post_message-wrap',
                ) ) ),
                'value'               => get_option( $this->options_keys->what_happens_when_post_is_restricted ),
            ) );
        }

        public function post_url_redirection_field_view() {
            vpr_helpers()->input( array(
                'id'      => $this->options_keys->post_url_redirection,
                'name'    => $this->options_keys->post_url_redirection,
                'value'   => get_option( $this->options_keys->post_url_redirection, '' ),
                'type'    => 'text',
                'wrapped' => false,
            ) );
            ?>
			<p><?php 
            _e( 'You can enter a full URL or a path (for example: /test/)', 'wp-conditional-post-restrictions' );
            ?></p>
			<?php 
        }

        public function restricted_post_message_field_view() {
            $editor_content = get_option( $this->options_keys->restricted_post_message, '' );
            wp_editor( $editor_content, $this->options_keys->restricted_post_message, array(
                'textarea_rows' => 5,
            ) );
        }

        function instructions_view() {
            $prefix = '';
            $prefix = __( 'Premium: ', 'wp-conditional-post-restrictions' );
            ?>
			<ol>
				<li><?php 
            _e( 'Individual post restrictions: You will see the option to apply restrictions when you create or edit a post in the post editor.', 'wp-conditional-post-restrictions' );
            ?></li>
				<li><?php 
            echo $prefix . __( 'Global restrictions: You can restrict entire post types using a global settings page', 'wp-conditional-post-restrictions' );
            ?></li>				
				<li><?php 
            echo $prefix . __( 'Category and Tags restrictions: You can restrict category pages (listings)', 'wp-conditional-post-restrictions' );
            ?></li>				
			</ol>
			<?php 
        }

        public function what_happens_when_category_is_restricted_field_view() {
            vpr_helpers()->select( array(
                'id'                  => $this->options_keys->what_happens_when_category_is_restricted,
                'name'                => $this->options_keys->what_happens_when_category_is_restricted,
                'options'             => vpr_helpers()->get_field_options( '_' . $this->options_keys->what_happens_when_category_is_restricted ),
                'wrapped'             => false,
                'data-options_fields' => htmlspecialchars( json_encode( array(
                    'show_an_error_message'            => 'wp-vpr_error_message-wrap',
                    'redirect_to_another_url'          => 'vpr_category_url_redirection',
                    'show_the_content_of_another_page' => 'vpr_page',
                ) ) ),
                'value'               => get_option( $this->options_keys->what_happens_when_category_is_restricted, '' ),
            ) );
        }

        public function error_message_field_view() {
            $editor_content = get_option( $this->options_keys->error_message, '' );
            wp_editor( $editor_content, $this->options_keys->error_message, array(
                'textarea_rows' => 5,
            ) );
        }

        public function render_remove_posts_from_menus_field() {
            ?>
			<input type="hidden" name="vpr_hide_restricted_posts_from_menus" value=""/>			
			<input type="checkbox" name="vpr_hide_restricted_posts_from_menus" value="yes" <?php 
            checked( !empty( get_option( 'vpr_hide_restricted_posts_from_menus' ) ) );
            ?>/>			
			<?php 
        }

        public function category_url_redirection_field_view() {
            vpr_helpers()->input( array(
                'id'      => $this->options_keys->category_url_redirection,
                'name'    => $this->options_keys->category_url_redirection,
                'value'   => get_option( $this->options_keys->category_url_redirection, '' ),
                'type'    => 'url',
                'wrapped' => false,
            ) );
        }

        public function show_the_content_of_another_page_field_view() {
            vpr_helpers()->select( array(
                'id'      => $this->options_keys->show_the_content_of_another_page,
                'name'    => $this->options_keys->show_the_content_of_another_page,
                'options' => vpr_helpers()->get_field_options( '_' . $this->options_keys->show_the_content_of_another_page ),
                'wrapped' => false,
                'value'   => get_option( $this->options_keys->show_the_content_of_another_page, '' ),
            ) );
        }

    }

}