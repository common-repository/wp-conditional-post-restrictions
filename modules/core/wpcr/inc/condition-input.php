<?php

if (!trait_exists("Vegacorp_Condition_Input")) {

	trait Vegacorp_Condition_Input {

		private $input_type = "";
		private $other_attributes = "";

		public function get_input($name = "", $return_as_string = true, $value = "") {

			$input = "<input name = '" . esc_attr($name) . "' type = '" . esc_attr($this->input_type) . "' " . $this->other_attributes . " value = '" . esc_attr($value) . "' style = 'width: 150px; height: 28px;'>";

			if ($return_as_string) {
				return $input;
			} else {
				echo $input;
			}
		}

		public function setInput_type($input_type) {
			$this->input_type = $input_type;
		}

		public function getInput_type() {
			return $this->input_type;
		}

		public function setOther_attributes($other_attributes) {
			$this->other_attributes = $other_attributes;
		}

		public function getOther_attributes() {
			return $this->other_attributes;
		}

	}

}