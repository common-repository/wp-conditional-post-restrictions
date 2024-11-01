<?php

add_action( 'wpcpr/settings_page/intro', 'wpcpr_add_settings_page_intro' );
add_action( 'vpr_/metabox/after_conditions_group', 'wpcpr_add_settings_page_intro' );
function wpcpr_add_settings_page_intro() {
    include WPCPR_DIST_DIR . '/views/action-buttons.php';
}

add_filter( 'vg_plugin_sdk/assets/allowed_pages', 'wpcpr_enable_assets_on_settings_page' );
function wpcpr_enable_assets_on_settings_page(  $allowed_pages  ) {
    $allowed_pages[] = 'vpr_settings';
    return $allowed_pages;
}
