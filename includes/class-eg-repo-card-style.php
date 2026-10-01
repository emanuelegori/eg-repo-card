<?php
/**
 * EG_Repo_Card_Style
 * Builds the CSS for the appearance settings (backgrounds, border, shadow).
 *
 * I colori del testo non si impostano: si ricavano dallo sfondo, chiari su
 * sfondo scuro e scuri su sfondo chiaro. Con "Segui il browser del
 * visitatore" le regole scure vanno dentro @media (prefers-color-scheme: dark).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Style {

    /** Preset colours: [ light, dark ] */
    private const PRESETS = [
        'card_bg'   => [ 'light' => '#f8f8f8', 'dark' => '#1f2937' ],
        'button_bg' => [ 'light' => '#e0e0e0', 'dark' => '#374151' ],
    ];

    private const TEXT = [ 'light' => '#111827', 'dark' => '#f3f4f6' ];

    private const BORDER = [
        'light'       => 'rgba(0,0,0,.12)',
        'dark'        => 'rgba(255,255,255,.16)',
        'transparent' => 'rgba(128,128,128,.35)',
    ];

    private const SHADOW = [
        'light' => '0 4px 12px rgba(0,0,0,.05)',
        'dark'  => '0 4px 12px rgba(0,0,0,.4)',
    ];

    /**
     * Complete CSS for the current settings.
     *
     * @return string
     */
    public static function css(): string {
        $settings = EG_Repo_Card_Settings::all();

        $light = self::rules( $settings, 'light' );
        $dark  = self::rules( $settings, 'dark' );

        $css = $light;
        if ( $dark !== $light ) {
            $css .= '@media (prefers-color-scheme: dark){' . $dark . '}';
        }

        return $css;
    }

    /**
     * Background of a surface in a colour-scheme context, null if transparent.
     *
     * @param  array  $settings
     * @param  string $surface   'card_bg' or 'button_bg'.
     * @param  string $context   'light' or 'dark'.
     * @return string|null
     */
    private static function surface( array $settings, string $surface, string $context ): ?string {
        switch ( $settings[ $surface ] ) {
            case 'none':
                return null;
            case 'auto':
                return self::PRESETS[ $surface ][ $context ];
            case 'custom':
                return $settings[ $surface . '_color' ];
            default:
                return self::PRESETS[ $surface ]['light'];
        }
    }

    /**
     * 'dark' when a colour needs light text on top of it.
     *
     * @param  string $hex
     * @return string
     */
    public static function polarity( string $hex ): string {
        $hex = ltrim( $hex, '#' );
        if ( 3 === strlen( $hex ) ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if ( 6 !== strlen( $hex ) ) {
            return 'light';
        }

        $channels = [];
        foreach ( [ 0, 2, 4 ] as $offset ) {
            $c          = hexdec( substr( $hex, $offset, 2 ) ) / 255;
            $channels[] = ( $c <= 0.03928 ) ? $c / 12.92 : ( ( $c + 0.055 ) / 1.055 ) ** 2.4;
        }
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

        // Soglia in cui il contrasto con il testo scuro e con quello chiaro si equivale.
        return ( $luminance < 0.18 ) ? 'dark' : 'light';
    }

    /**
     * CSS rules for one colour-scheme context.
     *
     * @param  array  $settings
     * @param  string $context
     * @return string
     */
    private static function rules( array $settings, string $context ): string {
        $card   = self::surface( $settings, 'card_bg', $context );
        $button = self::surface( $settings, 'button_bg', $context );

        $card_polarity = $card ? self::polarity( $card ) : null;

        $card_rules = [
            'background' => $card ?? 'transparent',
            'color'      => $card_polarity ? self::TEXT[ $card_polarity ] : 'inherit',
            'border'     => $settings['border']
                ? '1px solid ' . self::BORDER[ $card_polarity ?? 'transparent' ]
                : 'none',
            // Una card trasparente non ha una superficie da staccare dalla pagina.
            'box-shadow' => ( $settings['shadow'] && $card ) ? self::SHADOW[ $card_polarity ] : 'none',
        ];

        if ( $button ) {
            $button_polarity = self::polarity( $button );
            $button_rules    = [
                'background'   => $button,
                'color'        => self::TEXT[ $button_polarity ],
                'border-color' => 'transparent',
            ];
        } else {
            $button_rules = [
                'background'   => 'transparent',
                'color'        => 'inherit',
                'border-color' => self::BORDER[ $card_polarity ?? 'transparent' ],
            ];
        }

        return '.eg-repo-card{' . self::declarations( $card_rules ) . '}'
            . '.eg-repo-card .eg-repo-card__btn,.eg-repo-card .eg-repo-card__btn:visited,.eg-repo-card .eg-repo-card__btn:hover{'
            . self::declarations( $button_rules ) . '}';
    }

    /**
     * @param  array $rules  property => value
     * @return string
     */
    private static function declarations( array $rules ): string {
        $out = '';
        foreach ( $rules as $property => $value ) {
            $out .= $property . ':' . $value . ';';
        }
        return $out;
    }
}
