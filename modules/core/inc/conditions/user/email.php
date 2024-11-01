<?php

if (!class_exists("WPCPR_User_Email")) {

	class WPCPR_User_Email extends Vegacorp_Condition {

		public function __construct() {
			$this->setInput_type("text");

			parent::__construct(
					"user_email", __("User email", 'wp-conditional-post-restrictions'), 'user', false, true
			);
		}

		use Vegacorp_Condition_Input;

		public function get_html_input($name = "", $return_as_string = true, $value = "") {

			return $this->get_input($name, $return_as_string, $value);
		}

		public function get_value_for_test($args) {

			extract($args);

			$out = '';
			if (is_user_logged_in()) {
				$user = get_userdata(get_current_user_id());
				$out = $user->user_email;
			}
			return $out;
		}

		public function prepare_values($val1, $val2) {
			return $this->prepare_non_numeric_values($val1, $val2);
		}

	}

	return new WPCPR_User_Email();
}