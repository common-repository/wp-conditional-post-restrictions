<?php

//Global constants
if (!defined("VEGACORP_CONDITIONS_TEXT_DOMAIN")) {
	define("VEGACORP_CONDITIONS_TEXT_DOMAIN", 'vegacorp_conditions_text_domain');
}

if (!defined("VEGACORP_CONDITIONS_PATH")) {
	define("VEGACORP_CONDITIONS_PATH", plugin_dir_path(__FILE__));
}

if (!defined("VEGACORP_CONDITIONS_URL")) {
	define("VEGACORP_CONDITIONS_URL", plugin_dir_url(__FILE__));
}

require_once VEGACORP_CONDITIONS_PATH . "inc/helpers.php";
require_once VEGACORP_CONDITIONS_PATH . "inc/enqueues.php";
require_once VEGACORP_CONDITIONS_PATH . "views/backend/conditions-metaboxes-html.php";
require_once VEGACORP_CONDITIONS_PATH . "inc/condition-input.php";
require_once VEGACORP_CONDITIONS_PATH . "inc/condition-select.php";
require_once VEGACORP_CONDITIONS_PATH . "inc/condition.php";
require_once VEGACORP_CONDITIONS_PATH . "views/backend/settings-views.php";
require_once VEGACORP_CONDITIONS_PATH . "frontend/filter.php";
require_once VEGACORP_CONDITIONS_PATH . "inc/init.php";

//require_once WPCPG_PATH . "backend/settings.php";
//require_once WPCPG_PATH . "backend/conditions-post-type.php";
//require_once WPCPG_PATH . "backend/conditions-metaboxes.php";

/*require_once WPCPG_PATH . "frontend/filter.php";
require_once WPCPG_PATH . "frontend/reload-checkout.php";*/

//register_activation_hook(__FILE__, "WP_CPG::activation_hook");




