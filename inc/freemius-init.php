<?php

if ( !function_exists( 'wpcpr_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wpcpr_fs() {
        global $wpcpr_fs;
        if ( !isset( $wpcpr_fs ) ) {
            // Include Freemius SDK.
            require_once WPCPR_DIST_DIR . '/vendor/freemius/start.php';
            $wpcpr_fs = fs_dynamic_init( array(
                'id'             => '6941',
                'slug'           => 'wp-conditional-post-restrictions',
                'type'           => 'plugin',
                'public_key'     => 'pk_521e36f9c9588bef66d48878201bb',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'       => 'vpr_settings',
                    'first-path' => 'options-general.php?page=vpr_settings',
                    'support'    => false,
                    'parent'     => array(
                        'slug' => 'options-general.php',
                    ),
                ),
                'is_live'        => true,
            ) );
        }
        return $wpcpr_fs;
    }

    // Init Freemius.
    wpcpr_fs();
    // Signal that SDK was initiated.
    do_action( 'wpcpr_fs_loaded' );
}