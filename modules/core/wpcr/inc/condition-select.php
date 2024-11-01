<?php

if (!trait_exists("Vegacorp_Select_Input")) {

	trait Vegacorp_Select_Input {

		private $options = "";
		private $html_options;
		private $default_option = "";
		private $request_options_with_ajax;
		private $ajax_method;
		private $is_select_2 = false;

		public function get_select($name = "", $return_as_string = true, $selected_option = "", $optgroups = false, $data = "") {

			$select_options = "";

			if (!$optgroups) {
				$select_options = $this->getConditions_handler()->get_options_html_formatted($this->combine_options(), $selected_option);
			} else {
				$select_options = $this->getConditions_handler()->get_optgroups_options_html_formatted(array_merge($this->default_option, $this->options), $selected_option);
			}

			$select_2 = "";

			if ($this->is_select_2) {
				$select_2 = 'class="vegacorp-select-2"';
			}

			$control_data = '';

			if (!empty($data)) {
				$select_data = "data-control_data = " . esc_attr(json_encode($data));
			}

			$input = "<select name = '" . esc_attr($name). "'  $select_2 $control_data style = 'width: 150px; height: 22px;'>" . $select_options . "</select>";

			if ($return_as_string) {
				return $input;
			} else {
				echo $input;
			}
                        
		}

		public function setOptions($options) {
			$this->options = $options;
		}

		public function getOptions() {
			return $this->options;
		}

		public function setDefault_option($default_option) {
			$this->default_option = $default_option;
		}

		public function getDefault_option() {
			return $this->default_option;
		}

		public function setRequest_options_with_ajax($request_options_with_ajax) {
			$this->request_options_with_ajax = $request_options_with_ajax;
		}

		public function getRequest_options_with_ajax() {
			return $this->request_options_with_ajax;
		}

		public function setAjax_method($ajax_method) {
			$this->ajax_method = $ajax_method;
		}

		public function getAjax_method() {
			return $this->ajax_method;
		}

		function getIs_select_2() {
			return $this->is_select_2;
		}

		function setIs_select_2($is_select_2) {
			$this->is_select_2 = $is_select_2;
		}

		public function combine_options() {

			$options = array();

                        if(empty($this->default_option)){ $this->default_option = array(); }
                        
			foreach ($this->default_option as $default_option_key => $option_default) {

				$options[$default_option_key] = $option_default;
			}

			foreach ($this->options as $option_key => $option) {

				$options[$option_key] = $option;
			}

			return $options;
		}

	}

}

