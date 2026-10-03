<?php
/**
 * EG_Repo_Card_Main
 * Bootstraps the plugin: shortcode and admin page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Main {

    public static function init(): void {
        // Register shortcode and front-end assets
        EG_Repo_Card_Shortcode::register();

        // Admin settings page
        if ( is_admin() ) {
            EG_Repo_Card_Admin::init();
        }
    }
}
