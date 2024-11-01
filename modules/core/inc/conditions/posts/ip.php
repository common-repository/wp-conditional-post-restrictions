<?php

if (!class_exists('VPR_Ip_Condition')) {

	class VPR_Ip_Condition extends Vegacorp_Condition {

		private $ip_regex = '\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b';

		public function __construct() {

			$this->setInput_type('text');

			parent::__construct(
					'ip', __('IP', 'wp-conditional-post-restrictions'), 'posts', false, true
			);
		}

		use Vegacorp_Condition_Input;

		public function get_value_for_test($data) {

			return vpr_helpers()->get_user_ip();
		}

		public function get_html_input($name = "", $return_as_string = true, $value = "") {

			if ($return_as_string) {
				return $this->get_input($name, $return_as_string, $value);
			}

			$this->get_input($name, $return_as_string, $value);
		}

		public function prepare_values($condition_value, $user_ip) {
			return parent::prepare_non_numeric_values($condition_value, $user_ip);
		}

		public function is_valid($operator, $condition_value, $user_ip) {

			$condition_value = $this->sanitize_data($condition_value, $operator);
			$user_ip = $this->ip_is_valid($user_ip) ? $user_ip : '';
			$condition_value = implode(';', $this->_expand_ip_ranges($condition_value));
			return parent::is_valid($operator, $condition_value, $user_ip);
		}

		function _expand_ip_ranges($ips) {
			$new_ips = array();
			if (is_string($ips)) {
				$ips = explode(';', $ips);
				$ips = array_map('trim', $ips);
			}
			foreach ($ips as $ip) {
				if (strpos($ip, '/') === false) {
					$new_ips[] = $ip;
				} else {
					$ip_parts = explode('.', $ip);
					$range_ip_parts = array();
					$range_part_index = null;
					foreach ($ip_parts as $index => $ip_part) {
						if (strpos($ip_part, '/') !== false) {
							$range_parts = explode('/', $ip_part);
							foreach (range($range_parts[0], $range_parts[1]) as $range_number) {
								$range_ip_parts[] = $range_number;
							}
							$range_part_index = $index;
						}
					}
					foreach ($range_ip_parts as $range_ip_part) {
						$ip_parts[$range_part_index] = $range_ip_part;
						$new_ips[] = implode('.', $ip_parts);
					}
				}
			}
			$ips = $new_ips;
			return $ips;
		}

		public function sanitize_data($ip, $operator = '') {

			if (!is_string($ip)) {
				return '';
			}

			if ($operator === 'appears_in_this_list') {

				$ips = is_string($ip) ? explode(';', $ip) : array();
				$ips = array_map('trim', $ips);

				$ips = array_filter($ips, array($this, 'ip_is_valid'));

				return implode(';', $ips);
			}

			if (in_array($operator, array('equal_to', 'not_equal_to'))) {
				return $this->ip_is_valid($ip) ? $ip : '';
			}

			return parent::sanitize_data($ip);
		}

		public function ip_is_valid($ip) {
			// The regex check doesn't work with the ip ranges syntax so we're disabling the regex check temporarily
			return true;

			if (!is_string($ip)) {
				return false;
			}

			$ip_regex = '/' . $this->ip_regex . '/';

			return boolval(preg_match($ip_regex, $ip));
		}

	}

	return new VPR_Ip_Condition();
}



