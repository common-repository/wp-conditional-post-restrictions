<?php
//Class that filter the payment gateways in the frontend
if (!trait_exists('Vegacorp_Conditions_Filter')) {

	trait Vegacorp_Conditions_Filter {

                private $successful_conditions_groups_keys;
                
                private $failed_conditions_groups_keys;            
                              
		public function evaluate_conditions($conditions_groups_arrays) {
                                          
                        $this->successful_conditions_groups_keys = array();
                        
                        $this->failed_conditions_groups_keys     = array();
                        
			$conditions_keys                         = array_keys($this->conditions);		
                        
                        //Groups test starts equal to false
	                $groups_successful = empty($conditions_arrays);

			//Foreach post of conditions (vg payment gateways conditions)
			foreach ($conditions_groups_arrays as $conditions_groups_array_index => $conditions_array) {

				//Getting groups of current vg payment gateways conditions post type
				$groups = $this->get_active_conditions_saved_data($conditions_array, $conditions_keys);

				if (empty($groups)) {
					continue;
				}

				//Groups test starts equal to false
				$groups_successful = false;

				//Foreach groups of the current post 
				foreach ($groups as $key => $group) {

					//Getting conditions of the current group
					$group_conditions = $group["conditions"];

					if (empty($group_conditions)) {
						continue;
					}

					//Conditions test starts equal to true
					$conditions_test = true;

					//Foreach condition
					foreach ($group_conditions as $i => $condition) {

						extract($condition, EXTR_OVERWRITE);

						if (in_array($type, $conditions_keys)) {
							$current_condition_test = $this->conditions[$type]->condition_is_valid(
									array(
										'data'      => $this->get('data_source'),
										'condition' => $condition
									)
							);
						}

						$conditions_test = $conditions_test && $current_condition_test;
                                         
					}

					//Realizing "or" boolean operation for current group 
					$groups_successful = $groups_successful || $conditions_test;                                        
                                        
				}
                                
                                if($groups_successful){
                                    $this->successful_conditions_groups_keys[] = $conditions_groups_array_index;
                                }else{
                                    $this->failed_conditions_groups_keys[] = $conditions_groups_array_index;
                                }
		
			}
                        
		}	

		
	}

}

