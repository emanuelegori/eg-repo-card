<?php
/**
 * EG_Repo_Card_Main
 * Bootstraps the plugin: settings import, shortcode, admin page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Main {

    public static function init(): void {
        // Import the settings of EG Ranking Repo 1.x, once
        add_action( 'init', [ 'EG_Repo_Card_Migration', 'maybe_run' ], 5 );

        // Register shortcode and front-end assets
        EG_Repo_Card_Shortcode::register();

        // Admin settings page
        if ( is_admin() ) {
            EG_Repo_Card_Admin::init();
        }
    }
}
