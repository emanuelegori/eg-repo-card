<?php
/**
 * Plugin Name:       EG Repo Card
 * Plugin URI:        https://git.emanuelegori.uno/emanuelegori/eg-repo-card
 * Gitea Plugin URI:  https://git.emanuelegori.uno/emanuelegori/eg-repo-card
 * Description:       Display a card with the data of a GitHub, Codeberg, Forgejo or Gitea repository: description, latest release, download link, language, stars.
 * Version:           2.0.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Emanuele Gori
 * Author URI:        https://emanuelegori.uno
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eg-repo-card
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'EG_REPO_CARD_VERSION', '2.0.1' );
define( 'EG_REPO_CARD_FILE',    __FILE__ );
define( 'EG_REPO_CARD_DIR',     plugin_dir_path( __FILE__ ) );
define( 'EG_REPO_CARD_URL',     plugin_dir_url( __FILE__ ) );

// Load classes
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-settings.php';
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-migration.php';
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-api.php';
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-style.php';
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-shortcode.php';
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-admin.php';
require_once EG_REPO_CARD_DIR . 'includes/class-eg-repo-card-main.php';

// Bootstrap
EG_Repo_Card_Main::init();
