<?php
/**
 * Plugin Name:       EG Ranking Repo
 * Plugin URI:        https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo
 * Forgejo Plugin URI: emanuelegori/eg-ranking-repo
 * Description:       Mostra una card con i dati di un repository GitHub o Forgejo tramite shortcode [eg-ranking-repo url="..."].
 * Version:           1.2.4
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Emanuele Egori
 * Author URI:        https://emanuelegori.uno
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eg-ranking-repo
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'EGR_VERSION',     '1.2.4' );
define( 'EGR_PLUGIN_FILE', __FILE__ );
define( 'EGR_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'EGR_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'EGR_FORGEJO_SLUG', 'eg-ranking-repo' );

// Load classes
require_once EGR_PLUGIN_DIR . 'includes/class-egr-api.php';
require_once EGR_PLUGIN_DIR . 'includes/class-egr-shortcode.php';
require_once EGR_PLUGIN_DIR . 'includes/class-egr-settings.php';
require_once EGR_PLUGIN_DIR . 'includes/class-egr-main.php';

// Bootstrap
EGR_Main::init();
