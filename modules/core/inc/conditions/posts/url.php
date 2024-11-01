<?php

if (!class_exists('VPR_Url_Condition')) {

	class VPR_Url_Condition extends Vegacorp_Condition {

		public function __construct() {

			$this->setInput_type('text');

			parent::__construct(
					'url', __('Url', 'wp-conditional-post-restrictions'), 'posts', false, true
			);
		}

		use Vegacorp_Condition_Input;

		public function get_value_for_test($data) {

			global $wp;

			return home_url($wp->request);
		}

		public function get_html_input($name = "", $return_as_string = true, $value = "") {

			if ($return_as_string) {
				return $this->get_input($name, $return_as_string, $value);
			}

			$this->get_input($name, $return_as_string, $value);
		}

		public function prepare_values($condition_value, $current_url) {

			return $this->prepare_non_numeric_values($condition_value, $current_url);
		}

	}

	return new VPR_Url_Condition();
}

