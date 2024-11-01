<?php

if (!class_exists('VPR_Autoloader')) {

	class VPR_Autoloader {

		private $autoloader_files;
		private $plugin_prefix = 'VPR_';

		public function __construct() {

			$this->autoloader_files = vpr_helpers()->get_dir_files(VPR_PATH);
			spl_autoload_register(array($this, 'autoload'));
		}

		/**
		 * autoloads php files depending of the text that php passes to the function
		 * 
		 * @param string $php_text the text to know which file we must require
		 * 
		 */
		public function autoload($php_text) {

			/*
			 * We use scores instead of underscores to name files, so, to autoload a file 
			 * the php text must be named like the file but instead of scores use underscores 
			 * and the plugin prefix at the beginning of the text, example:
			 * text: VPR_Settings, file settings.php
			 * 
			 */

			//Removing the prefix from the text
			$file_name = str_replace($this->plugin_prefix, '', $php_text);
			$file_name = str_replace('_', '-', strtolower($file_name));

			foreach ($this->autoloader_files as $file_path) {

				$file_path_pieces = explode('/', $file_path);

				$file_path_name_pieces = explode('.', end($file_path_pieces));

				if (reset($file_path_name_pieces) === $file_name) {
					require $file_path;
					break;
				}
			}
		}

	}

	new VPR_Autoloader();
}
