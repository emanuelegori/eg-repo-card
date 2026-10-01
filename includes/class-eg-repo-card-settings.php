<?php
/**
 * EG_Repo_Card_Settings
 * Option names, defaults and read access to the stored settings.
 *
 * Le impostazioni non segrete stanno in un'unica option array; i token
 * restano in option separate perche' il campo vuoto significa "non cambiare"
 * e hanno un pulsante di rimozione dedicato.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Settings {

    public const OPTION        = 'eg_repo_card_settings';
    public const GITHUB_TOKEN  = 'eg_repo_card_github_token';
    public const FORGEJO_TOKEN = 'eg_repo_card_forgejo_token';
    public const CACHE_GEN     = 'eg_repo_card_cache_gen';

    /** Background modes shared by the card and the buttons */
    public const BG_MODES = [ 'neutral', 'none', 'auto', 'custom' ];

    /** Default values of the settings array */
    public const DEFAULTS = [
        'cache_hours'     => 6,
        'card_bg'         => 'neutral',
        'card_bg_color'   => '#f8f8f8',
        'button_bg'       => 'neutral',
        'button_bg_color' => '#e0e0e0',
        'border'          => 1,
        'shadow'          => 0,
        'avatar'          => 0,
    ];

    /**
     * Stored settings merged over the defaults.
     *
     * @return array
     */
    public static function all(): array {
        $stored = get_option( self::OPTION, [] );
        if ( ! is_array( $stored ) ) {
            $stored = [];
        }
        return array_merge( self::DEFAULTS, array_intersect_key( $stored, self::DEFAULTS ) );
    }

    /**
     * Single setting value.
     *
     * @param  string $key
     * @return mixed
     */
    public static function get( string $key ) {
        $all = self::all();
        return $all[ $key ] ?? null;
    }

    /**
     * Sanitise the settings array submitted from the admin page.
     *
     * @param  mixed $input
     * @return array
     */
    public static function sanitize( $input ): array {
        $input  = is_array( $input ) ? $input : [];
        $output = self::DEFAULTS;

        $output['cache_hours'] = max( 1, min( 168, (int) ( $input['cache_hours'] ?? self::DEFAULTS['cache_hours'] ) ) );

        foreach ( [ 'card_bg', 'button_bg' ] as $key ) {
            $mode           = sanitize_key( $input[ $key ] ?? '' );
            $output[ $key ] = in_array( $mode, self::BG_MODES, true ) ? $mode : self::DEFAULTS[ $key ];

            $color_key            = $key . '_color';
            $color                = sanitize_hex_color( $input[ $color_key ] ?? '' );
            $output[ $color_key ] = $color ? $color : self::DEFAULTS[ $color_key ];
        }

        foreach ( [ 'border', 'shadow', 'avatar' ] as $key ) {
            $output[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
        }

        return $output;
    }

    /**
     * Sanitise a token field. An empty field keeps the stored token.
     *
     * @param  mixed  $value
     * @param  string $option
     * @return string
     */
    public static function sanitize_token( $value, string $option ): string {
        $new = sanitize_text_field( (string) $value );
        if ( '' === $new ) {
            return (string) get_option( $option, '' );
        }
        return $new;
    }
}
