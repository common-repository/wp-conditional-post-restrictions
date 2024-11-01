<?php

if (!class_exists("WPCPR_User_Role")) {

	class WPCPR_User_Role extends Vegacorp_Condition {

		public function __construct() {

			$this->setIs_select_2(true);
			$this->add_to_condition_assoc("is_select_2", $this->getIs_select_2());

			parent::__construct(
					"user_role", __("User role", 'wp-conditional-post-restrictions'), 'user', false, true
			);
		}

		use Vegacorp_Select_Input;

		public function get_html_input($name = "", $return_as_string = true, $selected_option = "") {
			$this->setOptions(vpr_helpers()->get_user_roles());
			$this->setDefault_option(array("" => __("Select role", 'wp-conditional-post-restrictions')));
			return $this->get_select($name, $return_as_string, $selected_option);
		}

		public function get_value_for_test($args) {

			extract($args);
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$role = implode(',', $current_user->roles);
			} else {
				$role = 'guest';
			}

			return $role;
		}

		public function prepare_values($val1, $val2) {
			if ($val1 == 'guest') {
				return array("val1" => $val1, "val2" => is_user_logged_in() ? "not_guest" : "guest");
			}

			return $this->prepare_non_numeric_values($val1, $val2);
		}

	}

	return new WPCPR_User_Role();
}