<?php

if (!class_exists('VPR_Posts_Restrictions_Handler')) {

	class VPR_Posts_Restrictions_Handler {

		/**
		 * @var array $meta_keys The meta keys used in this plugin
		 */
		private $meta_keys;

		/**
		 * @var array $options The options keys used in this plugin
		 */
		private $options;

		public function __construct() {

			$this->meta_keys = (object) vpr_helpers()->get_meta_keys();
			$this->options = (object) vpr_helpers()->get_meta_keys(true);
			$this->init();
		}

		public function get_redirect_to_url($post) {
			$post_meta_redirect_to = get_post_meta($post->ID, $this->meta_keys->post_url_redirection, true);
			$url_to_redirect = $post_meta_redirect_to ? $post_meta_redirect_to : get_option($this->options->post_url_redirection);
			if (!empty($url_to_redirect)) {
				$url_to_redirect = strpos($url_to_redirect, 'http') === 0 ? filter_var($url_to_redirect, FILTER_VALIDATE_URL) : home_url($url_to_redirect);
			} else {
				$url_to_redirect = home_url();
			}
			return $url_to_redirect;
		}

		/**
		 * Performs a redirection if the post is restricted 
		 */
		public function redirect_from_restricted_post() {
			//If is not a post page we do nothing
			if (!is_singular()) {
				return;
			}

			global $post;

			//Getting the url for redirection
			$url_to_redirect = $this->get_redirect_to_url($post);

			//Defining if the option to redirect is activated
			$redirect_to_url = get_option($this->options->what_happens_when_post_is_restricted) === 'redirect_to_url';

			//Trying to perform the redirection
			if (vpr_helpers()->is_restricted($post) && $redirect_to_url) {
				wp_redirect(esc_url_raw($url_to_redirect));
				exit();
			}
		}

		/**
		 * Removes part of the contest of a post if it is restricted
		 * 
		 * @param string $the_content The content of the post from which we want to remove a fragment()
		 * 
		 * @return string The post content with part of it removed
		 * 
		 */
		public function filter_the_content_of_restricted_post($the_content) {


			$redirect_to_url = get_option($this->options->what_happens_when_post_is_restricted) === 'redirect_to_url';
			//Getting the post
			$post = get_post(get_the_ID());
			//Defining if the post is restricted
			$is_restricted = vpr_helpers()->is_restricted($post);

			if ($redirect_to_url && $is_restricted) {
				$url_to_redirect = $this->get_redirect_to_url($post);
				return '<script>window.location.href=' . json_encode(esc_url($url_to_redirect)) . ';</script>';
			}


			//Getting the message to append to the fragment of the content
			$message = wpautop(get_option($this->options->restricted_post_message, ''));

			//Defining if the content must completely removed
			$remove_the_content = get_option($this->options->what_happens_when_post_is_restricted) === 'remove_content_and_show_message';

			//Defining if only a fragment of the content must be removed
			$remove_part_of_the_content = get_option($this->options->what_happens_when_post_is_restricted) === 'show_fragment_of_the_content_and_show_message_after_fragment';
			//If the post is not restricted, then the content remains the same
			if (!$is_restricted) {
				return apply_filters('vpr_the_content', $the_content, $post);
			}

			//Removing the content of the post and replacing it with a message
			if ($remove_the_content) {
				return apply_filters('vpr_the_content', '<p>' . $message . '</p>', $post);
			}

			//Removing part of the content and appending a message
			if ($remove_part_of_the_content) {

				$fragment_of_the_content = $this->remove_part_of_the_content($the_content);

				return apply_filters('vpr_the_content', wpautop($fragment_of_the_content) . $message, $post);
			}

			//If the post is restricted we remove part of the content
			if ($is_restricted) {

				$fragment_of_the_content = $this->remove_part_of_the_content($the_content);

				return apply_filters('vpr_the_content', wpautop($fragment_of_the_content), $post);
			}
		}

		/**
		 * Gets the first p tag from post content
		 * 
		 * @param string $the_content the content of the post
		 * 
		 * @return string Part of the content concatened to a span tag with dots, all of this within a p tag
		 * 
		 */
		public function remove_part_of_the_content($the_content) {

			$excerpt = wp_trim_words($the_content, 30);
			return $excerpt;
		}

		function add_restriction_classes($classes, $class, $post_id) {

			$post = get_post($post_id);
			$is_restricted = vpr_helpers()->is_restricted($post);
			if ($is_restricted) {
				$classes[] = 'vpr-post-restricted';
			}
			return $classes;
		}

		function remove_posts_from_menu($items) {
			if (!get_option('vpr_hide_restricted_posts_from_menus')) {
				return $items;
			}

			$available_menu_items = array();
			foreach ($items as $index => $item) {
				if ($item->type === 'post_type' && vpr_helpers()->is_restricted(get_post((int) $item->object_id))) {
					continue;
				}

				$available_menu_items[$index] = $item;
			}

			return $available_menu_items;
		}

		/**
		 * Initializes the actions and filters to perform the restrictions in the frontend
		 */
		public function init() {

			$conditions_enabled = boolval(get_option('vpr_conditions_enabled', false));

			//If the conditions are disabled, then we do nothing
			if (!$conditions_enabled || is_admin()) {

				return;
			}

			add_action('template_redirect', array($this, 'redirect_from_restricted_post'));
			add_filter('the_content', array($this, 'filter_the_content_of_restricted_post'), 999);
			add_filter('post_class', array($this, 'add_restriction_classes'), 10, 3);
			add_filter('wp_get_nav_menu_items', array($this, 'remove_posts_from_menu'));
		}

	}

}
