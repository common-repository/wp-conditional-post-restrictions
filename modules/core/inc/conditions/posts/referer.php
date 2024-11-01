<?php

if (!class_exists('VPR_Referer_Condition')) {

	class VPR_Referer_Condition extends Vegacorp_Condition {

		public function __construct() {

			$this->setInput_type('text');

			parent::__construct(
					'referer', __('Referer', 'wp-conditional-post-restrictions'), 'posts', false, true
			);
		}

		use Vegacorp_Condition_Input;

		public function get_value_for_test($data) {

			if (!empty($data['referer'])) {
				return $data['referer'];
			}

			$referer = wp_get_referer();
			if (empty($referer) && !empty($_SERVER['HTTP_REFERER'])) {
				$referer = $_SERVER['HTTP_REFERER'];
			}

			return esc_url($referer);
		}

		public function get_html_input($name = "", $return_as_string = true, $value = "") {

			if ($return_as_string) {
				return $this->get_input($name, $return_as_string, $value);
			}

			$this->get_input($name, $return_as_string, $value);
		}

		public function prepare_values($condition_value, $referer) {
			return $this->prepare_non_numeric_values($condition_value, $referer);
		}

	}

	return new VPR_Referer_Condition();
}

