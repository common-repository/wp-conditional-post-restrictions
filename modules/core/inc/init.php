<?php

if (!class_exists('VC_Posts_Restrictions')) {

	class VC_Posts_Restrictions {

		use VC_Singleton;

		private $plugin_prefix = 'VPR_';
		private $restrictions_post_type;
		private $categories_fields;
		private $settings;
		private $posts_restrictions_handler;
		private $categories_restrictions_handler;

		public function late_init() {
			//Initializing the conditions handler
			vpr_helpers()->conditions_handler();
			$this->initialize_object_vars();
		}

		public function init() {

			// Exclude common bots from geolocation by user agent.
			$ua = strtolower(vpr_helpers()->get_user_agent());
			if (strstr($ua, 'bot') || strstr($ua, 'spider') || strstr($ua, 'crawl')) {
				return;
			}
			add_action('init', array($this, 'late_init'), 20);
		}

		/**
		 * Initializes this object properties
		 */
		public function initialize_object_vars() {

			/*
			 * The variable must be named like the class, the class must use underscores and must have the
			 * plugin prefix at the beginning for example: variable: $categories_restrictions_handler, class: VPR_Categories_Restrictions_Handler
			 */

			//Getting this object properties
			$object_vars = array_keys(get_object_vars($this));

			//instantiating the properties
			foreach ($object_vars as $object_var) {

				//Getting the property class
				$class = $this->plugin_prefix . implode('_', array_map('ucfirst', explode('_', $object_var)));
				if (vpr_helpers()->is_user_whitelisted() && strpos($class, 'Handler') !== false) {
					continue;
				}

				//Trying to instantiate the property
				if ($this->$object_var === null && class_exists($class)) {
					$this->$object_var = new $class();
				}
			}
		}

		function get_posts_restrictions_handler() {
			return $this->posts_restrictions_handler;
		}

		public static function activation_hook() {
			flush_rewrite_rules();
		}

	}

}

if (!function_exists('VPR')) {

	function VPR() {

		return VC_Posts_Restrictions::get_instance();
	}

}

VPR();
