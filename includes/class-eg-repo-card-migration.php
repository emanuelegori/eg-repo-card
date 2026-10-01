<?php
/**
 * EG_Repo_Card_Migration
 * One-time import of the settings saved by EG Ranking Repo 1.x.
 *
 * Copia (non sposta) le option egr_*: il vecchio plugin resta utilizzabile
 * finche' non viene disinstallato, ed e' la sua disinstallazione a cancellarle.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Migration {

    /** Defaults of EG Ranking Repo 1.x, used to tell custom colours from untouched ones */
    private const LEGACY_CARD_BG   = '#f8f8f8';
    private const LEGACY_BUTTON_BG = '#e0e0e0';

    public static function maybe_run(): void {
        if ( false !== get_option( EG_Repo_Card_Settings::OPTION ) ) {
            return;
        }

        $settings = EG_Repo_Card_Settings::DEFAULTS;

        $hours = get_option( 'egr_cache_hours', false );
        if ( false !== $hours ) {
            $settings['cache_hours'] = max( 1, min( 168, (int) $hours ) );
        }

        $legacy = [
            'card_bg'   => [ 'egr_card_bg_color', self::LEGACY_CARD_BG ],
            'button_bg' => [ 'egr_btn_bg_color',  self::LEGACY_BUTTON_BG ],
        ];
        foreach ( $legacy as $key => [ $option, $legacy_default ] ) {
            $color = sanitize_hex_color( (string) get_option( $option, '' ) );
            if ( $color && strtolower( $color ) !== $legacy_default ) {
                $settings[ $key ]            = 'custom';
                $settings[ $key . '_color' ] = $color;
            }
        }

        $tokens = [
            'egr_github_token'  => EG_Repo_Card_Settings::GITHUB_TOKEN,
            'egr_forgejo_token' => EG_Repo_Card_Settings::FORGEJO_TOKEN,
        ];
        foreach ( $tokens as $old => $new ) {
            $token = (string) get_option( $old, '' );
            if ( '' !== $token && '' === (string) get_option( $new, '' ) ) {
                update_option( $new, $token, false );
            }
        }

        update_option( EG_Repo_Card_Settings::OPTION, $settings );
    }
}
