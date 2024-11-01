<?php

if (!trait_exists("Vegacorp_Conditions_Helpers")) {

	//This class contains functions that returns associative arrays of options, also contains a function to format those arrays into html options
	trait Vegacorp_Conditions_Helpers {

		
		/*
		 * Function that return an array of options that are considered numeric, this is for know 
		 * which operators remove from operator select
		 */

		//Function that format a given array into html options
		public function get_options_html_formatted($options_array, $selected_option = "") {

			$options = "";

			//Checking if are multiple selected options		
			$check_multiple_selected_options = is_array($selected_option) && !empty($selected_option);

			foreach ($options_array as $key => $option) {

				//Here the key (or value) can be "" because it serves as default option in some cases
				if (empty($option)) {
					continue;
				}

				if ($check_multiple_selected_options) {
					//if are multiple selected options, the array of selected options is checked
					$selected = selected(in_array($key, $selected_option), true, false);
				} else {
					$selected = selected($selected_option == $key, true, false);
				}
				$options .= "<option value = '" . esc_attr($key) . "' " . $selected . ">$option</option>";
			}

			return $options;
                        
		}

		public function get_optgroups_options_html_formatted($optgroups, $selected_option = "") {

			$options = "";

			foreach ($optgroups as $group_key => $group_options) {
				if (empty($group_options)) {
					continue;
				}

				$current_group_open_tag = '<optgroup label = "' . esc_attr($group_key) . '">';
				$current_group_closed_tag = '</optgroup>';
				$current_group_options = $this->get_options_html_formatted($group_options, $selected_option);
				$options .= $current_group_open_tag . $current_group_options . $current_group_closed_tag;
			}

			return $options;
		}
		
		public function get_conditions_groups_html_options($selected_value = "") {
			
                        $conditions   = $this->get('conditions');                      
                        $groups       = $this->get('conditions_groups');
                    
			$options_html = "";

			foreach ($groups as $group_key => $group) {

				$current_group_open_tag = '<optgroup class = "' . esc_attr($group_key) . '" label = "' . esc_attr($group["label"]) . '">';
				$current_group_closed_tag = '</optgroup>';
				$current_group_options = '';

				foreach ($conditions as $condition_key => $condition) {
					if ($condition->getGroup() == $group_key) {
						$current_group_options .= $condition->get_html_option($selected_value);
					}
				}

				if (!empty($current_group_options)) {
					$options_html .= $current_group_open_tag . $current_group_options . $current_group_closed_tag;
				}
			}

			return apply_filters($this->prefix . 'conditions_groups_html_options', $options_html, $selected_value, $conditions, $groups);
		}

		public function get_active_conditions_saved_data($or_groups, $active_conditions) {
                    
			$groups = array();
                        
                        if(empty($or_groups) || !is_array($or_groups)){ return array(); }

			foreach ($or_groups as $or_group) {

				$group_conditions = array();

				foreach ($or_group["conditions"] as $condition) {

					if (in_array($condition["type"], $active_conditions)) {
						$group_conditions[] = $condition;
					}
				}

				if (!empty($group_conditions)) {
					$groups[]["conditions"] = $group_conditions;
				}
			}

			return $groups;
		}  

	}

}

