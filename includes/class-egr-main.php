<?php
/**
 * EGR_Main
 * Bootstraps the plugin: registers hooks, shortcode, admin, and the
 * EG Forgejo Updater integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EGR_Main {

    public static function init(): void {
        // Register shortcode and front-end assets
        EGR_Shortcode::register();

        // Admin settings page
        if ( is_admin() ) {
            EGR_Settings::init();
        }

        // Register this plugin with EG Forgejo Updater.
        // Fires after plugins are loaded so the updater plugin is already active.
        add_action( 'plugins_loaded', static function () {
            do_action( 'eg_forgejo_updater_register', EGR_PLUGIN_FILE, EGR_FORGEJO_SLUG );
        }, 20 );
    }
}
