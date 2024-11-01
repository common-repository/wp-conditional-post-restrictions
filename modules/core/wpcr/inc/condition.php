<?php

if (!class_exists("Vegacorp_Condition")) {

	abstract class Vegacorp_Condition {

		private $condition_key;
		private $label;
		private $group;
		private $operators;
		private $html_operators;
		private $is_numeric;
		private $html_input;
		private $condition_assoc;
		private $has_container_operators;                
                private $conditions_handler;

		public function __construct($condition_key, $label, $group, $is_numeric, $has_container_operators = false) {

			$this->condition_key           = $condition_key;
			$this->label                   = $label;
			$this->group                   = $group;
			$this->is_numeric              = $is_numeric;
			$this->has_container_operators = $has_container_operators;
			$this->init_operators();
			
		}

		abstract protected function get_value_for_test($args);

		abstract protected function get_html_input();

		abstract protected function prepare_values($val1, $val2);

		public function condition_is_valid($args) {                  
                         
			extract($args, EXTR_OVERWRITE);

			$operator = $condition['operator'];

			$val1 = $condition['data'];
			$val2 = $this->get_value_for_test($args);
                                      
			if (in_array($operator, array('equal_to_field', 'not_equal_to_field'), true)) {
				foreach ($this->conditions_handler->get('conditions') as $extra_condition) {
					if ($extra_condition->condition_key !== $val1) {
						continue;
					}
                                        
					$val1 = $extra_condition->get_value_for_test($args);      
                                      
				}
			}

			$prepared_values = $this->prepare_values($val1, $val2);
                             
			$val1 = $prepared_values['val1'];
			$val2 = $prepared_values['val2'];

			if ($operator === 'equal_to_field') {
				$operator = 'equal_to';
			} elseif ($operator === 'not_equal_to_field') {
				$operator = 'not_equal_to';
			}
                        
                        return apply_filters('vegacorp_condition_is_valid', $this->is_valid($operator, $val1, $val2), strtolower(get_class($this)), $this, $prepared_values, $args);
                        
		}

		public function get_html_option($value = "") {

			return '<option value = "' . esc_attr($this->condition_key) . '" ' . selected($value == $this->condition_key, true, false) . '>' . $this->label . '</option>';
		                        
                }

		public function init_operators() {

			$default_operators = array(
				"equal_to"           => __("=", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"not_equal_to"       => __("!= (Not equal)", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"equal_to_field"     => __("Equal to field", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"not_equal_to_field" => __("Not equal to field", VEGACORP_CONDITIONS_TEXT_DOMAIN),
			);

			$numeric_operators = array(
				"less_than"            => __("<", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"less_or_equal_than"   => __("<=", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"higher_than"          => __(">", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"higher_or_equal_than" => __(">=", VEGACORP_CONDITIONS_TEXT_DOMAIN)
			);

			$container_operators = array(
				"contains"             => __("Contains", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"not_contains"         => __("Not contains", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"appears_in_this_list" => __("Appears in this list", VEGACORP_CONDITIONS_TEXT_DOMAIN),
				"contains_keywords"    => __("Contains any of these keywords", VEGACORP_CONDITIONS_TEXT_DOMAIN),
			);

			if ($this->is_numeric) {
				$this->operators = array_merge($default_operators, $numeric_operators);
			} else {
				$this->operators = $default_operators;
			}

			if ($this->has_container_operators) {
				$this->operators = array_merge($this->operators, $container_operators);
			}
		}

		public function is_valid($operator, $val1, $val2) {

			switch ($operator) {

				case "equal_to":

					if (is_array($val2)) {
						$result = in_array($val1, $val2);
					} else {
						$result = $val1 === $val2;
					}

					break;

				case "not_equal_to":

					if (is_array($val2)) {
						$result = !in_array($val1, $val2);
					} else {
						$result = !($val1 === $val2);
					}

					break;

				case "less_than":
					$result = $val2 < $val1;
					break;

				case "less_or_equal_than":
					$result = $val2 <= $val1;
					break;

				case "higher_than":
					$result = $val2 > $val1;
					break;

				case "higher_or_equal_than":
					$result = $val2 >= $val1;
					break;

				case "contains":
					$result = strpos($val2, $val1);
					$result = $result === false ? $result : true;
					break;

				case "not_contains":
					$result = strpos($val2, $val1);
					$result = $result === false ? true : false;
					break;

				case "appears_in_this_list" :
					$val1   = explode(";", $val1);
					$result = !empty($val1) && is_array($val1) ? in_array(strtolower($val2), array_map("strtolower", array_map("trim", $val1))) : false;
					break;

				case "contains_keywords" :
					$val1   = implode(';', array_map('trim', explode(';', $val1)));
					$val1   = str_replace(';', '|', preg_quote( $val1, '/') );
					$result = !empty($val1) ? (bool) preg_match('/(' . $val1 . ')/i', $val2) : false;
					break;
                                    
			}
                            
			return $result;
                        
		}

		public function add_condition($conditions, $add_as_assoc_array = false) {

			if ($add_as_assoc_array) {

				$this->init_condition_assoc();
				$conditions[$this->condition_key] = $this->condition_assoc;
			} else {
				$conditions[$this->condition_key] = $this;
			}

			return $conditions;
		}
                
                public function sanitize_data($data, $operator = ''){
                    
                    $data = is_string($data) ? $data : '';
                    
                    return sanitize_text_field($data);
                    
                }

		function getCondition_key() {
			return $this->condition_key;
		}

		function getLabel() {
			return $this->label;
		}

		function getGroup() {
			return $this->group;
		}

		function getOperators() {
			return $this->operators;
		}

		function getIs_numeric() {
			return $this->is_numeric;
		}

		function setCondition_key($condition_key) {
			$this->condition_key = $condition_key;
		}

		function setLabel($label) {
			$this->label = $label;
		}

		function setGroup($group) {
			$this->group = $group;
		}

		function setOperators($operators) {
			$this->operators = $operators;
		}

		function setIs_numeric($is_numeric) {
			$this->is_numeric = $is_numeric;
		}

		function getHas_container_operators() {
			return $this->has_container_operators;
		}

		function setHas_container_operators($has_container_operators) {
			$this->has_container_operators = $has_container_operators;
		}            
                
		function getHtml_operators($selected_value = "") {
			if (empty($selected_value)) {
				return $this->html_operators;
			} else {
				return $this->conditions_handler->get_options_html_formatted($this->operators, $selected_value);
			}
		}

		function setHtml_operators() {
			$this->html_operators = $this->conditions_handler->get_options_html_formatted($this->operators);
		}

		public function setCondition_assoc($condition_assoc) {
			$this->condition_assoc = $condition_assoc;
		}

		public function getCondition_assoc() {
			return $this->condition_assoc;
		}
                
                function getConditions_handler() {
                    return $this->conditions_handler;
                }

                function setConditions_handler($conditions_handler) {
                    $this->conditions_handler = $conditions_handler;
                }
                
		public function init_condition_assoc() {
                    
			$this->condition_assoc["condition_key"] = $this->condition_key;
			$this->condition_assoc["label"] = $this->label;
			$this->condition_assoc["group"] = $this->group;
			$this->condition_assoc["operators"] = $this->operators;
			$this->condition_assoc["html_operators"] = $this->html_operators;
			$this->condition_assoc["is_numeric"] = $this->is_numeric;
			$this->condition_assoc["html_input"] = $this->get_html_input();                       
                        
			if(!empty($this->condition_assoc["operators"]['appears_in_this_list'])){
				$this->condition_assoc['operators_data']['appears_in_this_list']['placeholder'] = __('value 1; value 2; ... etc', VEGACORP_CONDITIONS_TEXT_DOMAIN);
			}
			if(!empty($this->condition_assoc["operators"]['contains_keywords'])){
				$this->condition_assoc['operators_data']['contains_keywords']['placeholder'] = __('Keyword 1; keyword 2; ... etc', VEGACORP_CONDITIONS_TEXT_DOMAIN);
			}


		}

		public function add_to_condition_assoc($key, $value) {
			$this->condition_assoc[$key] = $value;
		}

		public function prepare_float_values($val1, $val2) {

			$val1 = floatval($val1);

			if (is_array($val2)) {
				$val2 = array_map("floatval", $val2);
			} else {
				$val2 = floatval($val2);
			}

			return array(
				"val1" => $val1,
				"val2" => $val2
			);
                        
		}

		public function prepare_int_values($val1, $val2) {

			$val1 = intval($val1);

			if (is_array($val2)) {                                
				$val2 = array_map("intval", $val2);
			} else {
				$val2 = intval($val2);
			}

			return array(
				"val1" => $val1,
				"val2" => $val2
			);
		}

		public function prepare_non_numeric_values($val1, $val2) {

			$val1 = is_string($val1) ? strtolower($val1) : '';

			if (is_array($val2)) {
                                $val2 = array_filter($val2, 'is_scalar');
                                $val2 = array_map('strval', $val2);
				$val2 = array_map('strtolower', $val2);
			} else {
				$val2 = is_string($val2) ? strtolower($val2) : '';
			}

			return array(
				'val1' => $val1,
				'val2' => $val2
			);
                        
		}
                
               public function remove_operators($operators_keys = array(), $action = 'remove_the_given_operators'){
                   
                   $actions   = array('remove_the_given_operators', 'remove_other_operators');
                   $action    = in_array($action, $actions) ? $action : reset($actions); 
                   $operators = array();
                   
                   foreach($this->operators as $operator_key => $operator_text){
                       
                       if($action === 'remove_the_given_operators' && in_array($operator_key, $operators_keys) ){
                           continue;
                       }
                       
                       if($action === 'remove_other_operators' && !in_array($operator_key, $operators_keys)){                           
                           continue;
                       }
                       
                       $operators[$operator_key] = $operator_text;
                       
                   }
                   
                   $this->operators = $operators;
                   
               }

	}

}