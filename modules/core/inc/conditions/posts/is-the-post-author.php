<?php

if (!class_exists('VPR_Is_The_Post_Author_Condition')) {

	class VPR_Is_The_Post_Author_Condition extends Vegacorp_Condition {

		public function __construct() {

			$this->setOptions(array(
				'yes' => __('Yes', 'wp-conditional-post-restrictions'),
				'no' => __('No', 'wp-conditional-post-restrictions')
			));

			parent::__construct(
					'is_the_post_author', __('Is the post author', 'wp-conditional-post-restrictions'), 'posts', false
			);
		}

		use Vegacorp_Select_Input;

		public function init_operators() {

			parent::init_operators();

			$this->remove_operators(array('equal_to'), 'remove_other_operators');
		}

		public function get_html_input($name = '', $return_as_string = true, $selected_option = '') {

			$selected_option = $this->sanitize_data($selected_option);

			if ($return_as_string) {
				$select = $this->get_select($name, $return_as_string, $selected_option, false);
			} else {
				ob_start();
				$this->get_select($name, $return_as_string, $selected_option, false);
				$select = ob_get_clean();
			}

			if ($return_as_string) {
				return $select;
			}

			echo $select;
		}

		public function prepare_values($condition_value, $is_the_post_author) {

			return $this->prepare_non_numeric_values($condition_value, $is_the_post_author);
		}

		public function get_value_for_test($args) {

			if (!empty($args['data']['post'])) {
				$post = $args['data']['post'];
			}

			if (is_single() && empty($post)) {
				global $post;
			}

			$post = empty($post) ? get_post() : $post;
			$post_author = !empty($post) ? $post->post_author : 0;

			return intval($post_author) === intval(get_current_user_id()) ? 'yes' : 'no';
		}

	}

	return new VPR_Is_The_Post_Author_Condition();
}