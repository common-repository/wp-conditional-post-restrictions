<?php

if (!defined('VPR_PATH')) {
	define('VPR_PATH', plugin_dir_path(__FILE__));
}

if (!defined('VPR_URL')) {
	define('VPR_URL', plugin_dir_url(__FILE__));
}

require_once __DIR__ . '/wpcr/conditions.php';

$vpr_files_to_load = array(
	'inc/helpers',
	'inc/plugin-autoloader',
	'inc/trait-singleton',
	'inc/init'
);

foreach ($vpr_files_to_load as $vpr_file_to_load) {
	require_once __DIR__ . '/' . $vpr_file_to_load . '.php';
}

register_activation_hook(__FILE__, "VC_Posts_Restrictions::activation_hook");

