<?php

if (!class_exists("Vegacorp_Conditions_Handler")) {

	class Vegacorp_Conditions_Handler {               
                
                private $conditions_folder = '';          
                
                private $prefix = '';           
                
                private $conditions;
                
                private $conditions_assoc;
                
                private $conditions_groups;
                
                private $conditions_post_key = '';       
                
                private $conditions_meta_key = '';
                
                private $data_source = '';    
                
                use Vecagorp_Conditions_Metaboxes_Html;
                use Vegacorp_Conditions_Helpers;
                use Vegacorp_Conditions_Enqueues;
                use Vegacorp_Conditions_Settings_Views;
                use Vegacorp_Conditions_Filter;
                           
		function __construct($construct_args = array()) {
			
                    $default_args = array(
                         'conditions'        => array(),
                         'conditions_groups' => array(),
                         'conditions_assoc'  => array()
                    );
                    
                    $args = array_merge($default_args, $construct_args);
                    
                    foreach($args as $arg_key => $arg_value){
                        $this->set($arg_key, $arg_value);
                    }
                    
                    $this->init_default_script_obj_properties();
                    $this->init_conditions();                   
                    $this->succesful_conditions_groups_keys = array();
                    $this->failed_conditions_groups_keys    = array();
                    
                    $this->conditions_meta_key = !empty($this->conditions_meta_key) ? $this->conditions_meta_key : $this->conditions_post_key;
                    
		}
                
                public function get($key){                   
                    
                    if(!property_exists($this, $key)){ return null; }
                    
                    if(!empty($this->prefix)){ 
                        return apply_filters($this->prefix . $key, $this->$key, $key);                        
                    }
                    
                    return $this->$key;
                    
                }
                
                public function set($key, $value){
                    
                    if(property_exists($this, $key)){
                        $this->$key = $value;
                    }
                    
                }

		public function remove_directory_dots($files) {

			array_shift($files);
			array_shift($files);

			return $files;
		}

		public function init_conditions() {
             
			$conditions_folders = $this->remove_directory_dots(scandir($this->conditions_folder));

			foreach ($conditions_folders as $condition_folder) {

				$condition_folder_path = $this->conditions_folder . "/" . $condition_folder;
				$condition_folder_files = $this->remove_directory_dots(scandir($condition_folder_path));

				if (empty($condition_folder_files)) {
					continue;
				}

				foreach ($condition_folder_files as $condition_folder_file) {
			            
                                    $condition = require_once $this->conditions_folder . "/" . $condition_folder . "/" . $condition_folder_file;
                                 
                                    if(!is_a($condition, 'Vegacorp_Condition')){ continue; }
                                   
                                    $this->conditions[$condition->getCondition_key()] = $condition;
                                    
                                }
                                
			}
                  
				  $this->conditions = apply_filters('wpcr_registered_conditions', $this->conditions, $this);
                        array_walk($this->conditions, array($this, 'init_condition'));
                        array_walk($this->conditions, array($this, 'init_condition_assoc'));
                    
		}
                
                public function get_condition_assoc($condition){                    
                    
                    return $condition->getCondition_assoc();
                    
                }              
                
                public function init_condition_assoc($condition){
                    
                    $this->conditions_assoc[$condition->getCondition_key()] = $condition->getCondition_assoc();
                    
                }
                
                public function init_condition($condition){
                    
                    $condition->setConditions_handler($this);
                    $condition->setHtml_operators();
                    $condition->init_condition_assoc();
                    
                }	
                
                public function delete_conditions() {
                    
			if (!current_user_can('manage_options') || empty($_POST["post_id"])) {
				wp_send_json_success(array(
					"message"           => __("Invalid user role", VEGACORP_CONDITIONS_TEXT_DOMAIN),
					"condition_deleted" => 0
				));
			}

			//Getting post id
			$post_id = (int) $_POST["post_id"];

			//Checking nonce 
			check_ajax_referer("ajax_delete_conditions_security_nonce", "ajax_delete_conditions_nonce");

			//Flag for condition deletion
			//0 indicates fail and 1 indicates success
			$condition_deleted = 0;

			if (get_post_type($post_id) !== $this->conditions_post_key) {
				/*
				  If the post that is being deleted is not a of the type being handled 
				  a Warning is send, condition deleted flag is zero
				 */
				wp_send_json_success(array(
					"message" => __("Invalid post type deleting", VEGACORP_CONDITIONS_TEXT_DOMAIN),
					"condition_deleted" => $condition_deleted
				));
			}

			if (!wp_delete_post($post_id, true)) {
				//If post elimination fails an error message is send and condition deleted flag is set to zero
				$message = __("An error ocurred while deleting", VEGACORP_CONDITIONS_TEXT_DOMAIN);
				$condition_deleted = 0;
			} else {
				//If post elimination is succesull a message is send and condition deleted flag is set to 1
				$message = __("Condition succefully deleted", VEGACORP_CONDITIONS_TEXT_DOMAIN);
				$condition_deleted = 1;
			}

			//Here the data is send 
			wp_send_json_success(array(
				"message" => $message,
				"condition_deleted" => $condition_deleted
			));
                        
		}
                
                public function sanitize_groups($groups) {

			if (empty($groups) || !is_array($groups)) {
				return $groups;
			}

			$sanitized_groups = array();

			foreach ($groups as $key => $group) {

				$group_conditions = $group["conditions"];

				foreach ($group_conditions as $index => $condition) {

                                        $type     = sanitize_text_field($condition["type"]);
                                        $operator = sanitize_text_field($condition["operator"]);
                                        $raw_data = !empty($condition["data"]) ? $condition["data"] : null;
                                        $data     = !empty($this->conditions[$type]) ? $this->conditions[$type]->sanitize_data($raw_data, $operator) : ''; 
                                        $sanitized_conditon = compact('type', 'operator', 'data');

					$sanitized_groups[$key]["conditions"][] = $sanitized_conditon;
				}
			}

			return $sanitized_groups;
		}
                
                public function add_delete_conditions_action(){
                  
                    add_action("wp_ajax_delete_" . $this->conditions_post_key  , array($this, "delete_conditions"));
                    
                }

	}

}

if(!function_exists('vegacorp_create_conditions_handler')){
    
    function vegacorp_create_conditions_handler($args = array()){
        
         return new Vegacorp_Conditions_Handler($args);
        
    }
    
}


