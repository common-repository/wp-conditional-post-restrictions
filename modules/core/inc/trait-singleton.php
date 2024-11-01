<?php

if (!trait_exists("VC_Singleton")) {

	trait VC_Singleton {

		private static $instance;

		private function __construct() {
			$this->init();
		}

		public static function get_instance() {
			if (!isset(self::$instance)) {
				$myclass = __CLASS__;
				self::$instance = new $myclass;
			}
			return self::$instance;
		}

		public function __clone() {
			trigger_error("Clonation of this object is forbidden", E_USER_ERROR);
		}

		public function __wakeup() {
			trigger_error("You can't unserealize an instance of " . get_class($this) . " class.");
		}

		public abstract function init();
	}

}