<?php

if (!trait_exists("Vegacorp_Conditions_Enqueues")) {

	trait Vegacorp_Conditions_Enqueues {            
                            
                private $script_obj;
                
                public function init_default_script_obj_properties(){
                    
                    $this->script_obj = array(
		          'text' => array(//Page texts
		               'too_many_and_conditions' => __('You have added a large number of AND conditions. Are you sure these conditions will be met together? If not, you should add them as OR conditions.', VEGACORP_CONDITIONS_TEXT_DOMAIN),
		               'condition_text'          => __('Condition', VEGACORP_CONDITIONS_TEXT_DOMAIN),
		               'group_text'              => __('Group', VEGACORP_CONDITIONS_TEXT_DOMAIN),		               
		               'go_to_settings'          => __('Go to conditions list', VEGACORP_CONDITIONS_TEXT_DOMAIN)
		          ),
		          'ajax' => array(//Ajax url and nonce
		               'url' => admin_url("admin-ajax.php"),
		               'ajax_request_nonce' => wp_create_nonce("ajax_request_nonce")
		          )
		    );
                    
                }
            
		public function enqueue_or_groups_assets(){                        

		        //Scripts enqueue
			wp_enqueue_script("vegacorp_conditions_add_condition_script", VEGACORP_CONDITIONS_URL . "assets/js/add-condition-page-script.js", array("jquery"));
			wp_enqueue_script("vegacorp_conditions_select2_js", VEGACORP_CONDITIONS_URL . "assets/vendor/select2-4.0.5/js/select2.min.js", array("jquery"));

			//Styles enqueue 
			wp_enqueue_style("vegacorp_conditions_select2_css", VEGACORP_CONDITIONS_URL . "assets/vendor/select2-4.0.5/css/select2.min.css");
			wp_enqueue_style("vegacorp_conditions_css", VEGACORP_CONDITIONS_URL . "assets/css/conditions-css.css");
			                        
			//Sending data to main script     
			wp_localize_script(
		              "vegacorp_conditions_add_condition_script", 
                              "vegacorp_conditions_obj",
                              apply_filters($this->prefix . 'script_obj', $this->script_obj)
			);
                        
                        do_action($this->prefix . 'after_or_groups_assets_enqueues');				
			
		}

		//Settings page enqueues	
		public function enqueue_settings_assets() {			
			
			//Scripts enqueue
			wp_enqueue_script("vegacorp_conditions_options_page_script", VEGACORP_CONDITIONS_URL . "assets/js/options-page-script.js", array("jquery"));

			//Sending data to main script
			wp_localize_script(
					'vegacorp_conditions_options_page_script', 
                                        'vegacorp_conditions_options_page_obj', 
                                        array(
				            'ajax' => array(//Ajax url and nonce
				            	"url" => admin_url("admin-ajax.php"),
				            	"ajax_delete_conditions_nonce" => wp_create_nonce("ajax_delete_conditions_security_nonce")
				            ),
				            "text" => array(//Page texts
				            	"delete_condition_error_message" => "An error has ocurred while deleting conditions"
				            )
					)
			);
		}

	}

}
