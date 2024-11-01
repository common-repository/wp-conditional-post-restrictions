<?php
if (!trait_exists("Vecagorp_Conditions_Metaboxes_Html")) {

	trait Vecagorp_Conditions_Metaboxes_Html {            
            
		public function render_groups($or_groups, $data = array()) {

	                $default_data = array(
                            'settings_link' => ''
                        );
                        
                        $groups_data             = array_merge($default_data, $data);

                        $active_conditions       = array_keys($this->get('conditions'));
                        
                        $conditions_data_encoded = htmlspecialchars(json_encode($this->get('conditions_assoc')));                        
                    
			if (!empty($or_groups)) {
				$or_groups = $this->get_active_conditions_saved_data($or_groups, $active_conditions);
			}
			?>         

			<script id="<?php esc_attr_e($this->conditions_meta_key); ?>-group-template" type="text/x-custom-template">
			<?php
			$this->conditions_group(0, null, true);
			?>
			</script>
			<script id="<?php esc_attr_e($this->conditions_meta_key); ?>-row-template" type="text/x-custom-template">
			<?php
			$this->condition_table_row(0, 0, null, true);
			?>
			</script>
                        
                        <div class="vegacorp-or-groups" data-conditions="<?php echo $conditions_data_encoded; ?>" data-conditions_name="<?php esc_attr_e($this->conditions_meta_key); ?>" data-settings_link="<?php esc_attr_e($groups_data['settings_link']); ?>"> 
				<?php
				if (!empty($or_groups)) {
					foreach ($or_groups as $group_index => $or_group) {
						$this->conditions_group($group_index, $or_group['conditions']);
					}
				}else {
					$this->conditions_group(0, null);
				}
				?>
			</div><br>    
			<button type = "button" class = "btn-add-group button-secondary"><?php echo __("Add 'Or' group", VEGACORP_CONDITIONS_TEXT_DOMAIN); ?></button> 
			<?php
                        
                        if(!empty($this->prefix)){
                            do_action($this->prefix . '/metabox/after_conditions_group', $active_conditions, $or_groups);
                        }
			
		}

		public function conditions_group($group_index = 0, $conditions = null, $is_template = false) {
                    
                        if(empty($this->conditions)){ return; }    
                    
			//Inserting group table
			$group_text = sanitize_text_field(__("Group", VEGACORP_CONDITIONS_TEXT_DOMAIN));
			?>			
			<table class = "or_group_table" style = "margin: auto; margin-bottom:20px;">
				<thead>
					<tr>                    
						<th><button type = "button" class = "btn-remove-group button-secondary">x</button></th>
						<th class = "group-text"><?php echo "$group_text " . ($group_index + 1); ?></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody class = "conditions">
					<?php
					//Inserting conditions	
					if ($is_template) {
						$this->condition_table_row(0, 0, null, true);
					} else {

						if (!empty($conditions)) {
							foreach ($conditions as $condition_index => $condition) {
								$this->condition_table_row($group_index, $condition_index, $condition);
							}
						} else {
							$this->condition_table_row();
						}
					}
					?>
				</tbody>  
			</table>
			<?php
		}

		public function condition_table_row($group = 0, $i = 0, $condition = null, $is_template = false) {
                        
			$conditions               = $this->conditions;
                        
                        if(empty($conditions)){ return; }
                        
			$conditions_keys          = array_keys($conditions);
			$first_condition_key      = $conditions_keys[0];
			$condition_type_name      = $this->conditions_meta_key . "[$group][conditions][$i][type]";
			$condition_operators_name = $this->conditions_meta_key . "[$group][conditions][$i][operator]";
			$condition_input_name     = $this->conditions_meta_key . "[$group][conditions][$i][data]";                        
              
			?>
			<tr class = "condition">
				<td class = "condition-name">
					<?php
					if (!$is_template) {
						$condition_text = __("condition", VEGACORP_CONDITIONS_TEXT_DOMAIN);
						echo $condition_text . " " . ($i + 1);
					}
					?>		     
				</td>
				<td class = "type-selection">
					<select name = "<?php
					if (!$is_template) {
						echo $condition_type_name;
					}
					?>" class = "condition-input-modifier">                     
							<?php
							if (!empty($condition)) {
								echo $this->get_conditions_groups_html_options($condition["type"]);
							} else {
								echo $this->get_conditions_groups_html_options();
							}
							?>                      
					</select>
				</td>
				<td class = "operator-selection">
					<select name = "<?php
					if (!$is_template) {
						echo $condition_operators_name;
					}
					?>" style = "width:156px;"  class = "condition-operators-selection">
							<?php
							if ($is_template) {
								echo $conditions[$first_condition_key]->getHtml_operators();
							} else {

								if (!empty($condition)) {
									echo $conditions[$condition["type"]]->getHtml_operators($condition["operator"]);
								} else {
									echo $conditions[$first_condition_key]->getHtml_operators();
								}
							}
							?>                      
					</select>
				</td>
                                <td class = "input-column" data-saved-value="<?php if (!empty($condition)){ echo !is_scalar($condition['data']) ? esc_attr(json_encode($condition["data"])) : esc_attr($condition['data']); } ?>">                  
					<?php
					if ($is_template) {
						echo $conditions[$first_condition_key]->get_html_input();
					} else {

						if (!empty($condition)) {                
                                                        
							echo $conditions[$condition["type"]]->get_html_input($condition_input_name, true, $condition["data"]);
						} else {
							echo $conditions[$first_condition_key]->get_html_input($condition_input_name);
						}
					}
					?>                     					
				</td>
				<td>
					<button type = "button" class = "btn-add-condition button-secondary">+</button>
				</td>		     
				<td>
					<button type = "button" class = "btn-remove-condition button-secondary">-</button>
				</td>
			</tr>
			<?php
		}		

	}

}


