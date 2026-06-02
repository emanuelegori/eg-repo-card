<?php
/**
 * EGR_Shortcode
 * Registers and renders the [eg-ranking-repo url="..."] shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EGR_Shortcode {

    public static function register(): void {
        add_action( 'init', static function () {
            add_shortcode( 'eg-ranking-repo', [ 'EGR_Shortcode', 'render' ] );
        } );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_style' ] );
    }

    /**
     * Register the stylesheet (lazy-enqueued only when shortcode is used).
     */
    public static function register_style(): void {
        wp_register_style(
            'eg-ranking-repo',
            EGR_PLUGIN_URL . 'assets/css/eg-ranking-repo.css',
            [],
            EGR_VERSION
        );
    }

    /**
     * Render the shortcode output.
     *
     * @param  array $atts  Shortcode attributes.
     * @return string       HTML markup.
     */
    public static function render( $atts ): string {
        $atts = shortcode_atts( [ 'url' => '' ], $atts, 'eg-ranking-repo' );

        if ( empty( $atts['url'] ) ) {
            return '<p class="egr-error">'
                . esc_html__( 'Attributo url mancante nello shortcode.', 'eg-ranking-repo' )
                . '</p>';
        }

        // Enqueue only when the shortcode is actually used
        wp_enqueue_style( 'eg-ranking-repo' );

        $data = EGR_API::fetch( $atts['url'] );

        if ( is_wp_error( $data ) ) {
            return sprintf(
                '<p class="egr-error">%s: %s</p>',
                esc_html__( 'Errore', 'eg-ranking-repo' ),
                esc_html( $data->get_error_message() )
            );
        }

        // Read colour options (set in admin settings page)
        $card_bg  = get_option( 'egr_card_bg_color',  '#f8f8f8' );
        $card_txt = get_option( 'egr_card_txt_color', '#111111' );
        $btn_bg   = get_option( 'egr_btn_bg_color',   '#e0e0e0' );
        $btn_txt  = get_option( 'egr_btn_txt_color',  '#111111' );

        $has_homepage    = ! empty( $data['homepage'] );
        $platform_label  = ( $data['platform'] === 'github' ) ? 'GitHub' : 'Forgejo';
        $stars_formatted = EGR_API::format_stars( $data['stars'] );
        $date_formatted  = EGR_API::format_date( $data['updated_at'] );
        $stars_raw       = number_format( $data['stars'], 0, ',', '.' );
        $stars_title     = sprintf(
            /* translators: %s: formatted star count */
            _n( '%s star', '%s stars', $data['stars'], 'eg-ranking-repo' ),
            $stars_raw
        );
        $version         = $data['version'] ?? '';

        // Inline CSS custom properties carry the admin colour choices
        // without polluting the global stylesheet
        $style = sprintf(
            '--egr-card-bg:%s;--egr-card-txt:%s;--egr-btn-bg:%s;--egr-btn-txt:%s;',
            esc_attr( $card_bg ),
            esc_attr( $card_txt ),
            esc_attr( $btn_bg ),
            esc_attr( $btn_txt )
        );

        ob_start();
        ?>
        <div class="egr-card" style="<?php echo esc_attr( $style ); ?>">

            <div class="egr-card__header">
                <span class="egr-card__name"><?php echo esc_html( $data['full_name'] ); ?></span>
                <span class="egr-card__platform egr-card__platform--<?php echo esc_attr( $data['platform'] ); ?>">
                    <?php echo esc_html( $platform_label ); ?>
                </span>
            </div>

            <?php if ( ! empty( $data['description'] ) ) : ?>
            <p class="egr-card__description"><?php echo esc_html( $data['description'] ); ?></p>
            <?php endif; ?>

            <div class="egr-card__actions">

                <?php if ( $has_homepage ) : ?>
                <a href="<?php echo esc_url( $data['homepage'] ); ?>"
                   class="egr-btn egr-btn--website"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php esc_attr_e( 'Sito web del progetto', 'eg-ranking-repo' ); ?>">
                    <?php echo self::icon_globe(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hardcoded interno ?>
                    <?php echo esc_html__( 'Sito Web', 'eg-ranking-repo' ); ?>
                </a>
                <?php else : ?>
                <span class="egr-btn egr-btn--disabled"
                      aria-disabled="true"
                      tabindex="-1"
                      title="<?php esc_attr_e( 'Nessun sito web impostato nel repository', 'eg-ranking-repo' ); ?>">
                    <?php echo self::icon_globe(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hardcoded interno ?>
                    <?php echo esc_html__( 'Nessun sito', 'eg-ranking-repo' ); ?>
                </span>
                <?php endif; ?>

                <a href="<?php echo esc_url( $data['repo_url'] ); ?>"
                   class="egr-btn egr-btn--source"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php esc_attr_e( 'Vai al repository', 'eg-ranking-repo' ); ?>">
                    <?php echo self::icon_code(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hardcoded interno ?>
                    <?php echo esc_html__( 'Source Code', 'eg-ranking-repo' ); ?>
                </a>

                <span class="egr-btn egr-btn--badge"
                      title="<?php esc_attr_e( 'Ultimo aggiornamento', 'eg-ranking-repo' ); ?>">
                    <?php echo self::icon_calendar(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hardcoded interno ?>
                    <?php echo esc_html( $date_formatted ); ?>
                </span>

                <?php if ( $version !== '' ) : ?>
                <span class="egr-btn egr-btn--badge"
                      title="<?php esc_attr_e( 'Ultima versione rilasciata', 'eg-ranking-repo' ); ?>">
                    <?php echo self::icon_tag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hardcoded interno ?>
                    <?php echo esc_html( $version ); ?>
                </span>
                <?php endif; ?>

                <span class="egr-btn egr-btn--badge"
                      title="<?php echo esc_attr( $stars_title ); ?>">
                    <?php echo self::icon_star(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hardcoded interno ?>
                    <?php echo esc_html( $stars_formatted ); ?>
                </span>

            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    // --- Inline SVG icons (no external font/CDN dependency) ---

    private static function icon_star(): string {
        return '<svg class="egr-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2
                             9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>';
    }

    private static function icon_calendar(): string {
        return '<svg class="egr-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8"  y1="2" x2="8"  y2="6"/>
                    <line x1="3"  y1="10" x2="21" y2="10"/>
                </svg>';
    }

    private static function icon_code(): string {
        return '<svg class="egr-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"/>
                    <polyline points="8 6 2 12 8 18"/>
                </svg>';
    }

    private static function icon_tag(): string {
        return '<svg class="egr-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>';
    }

    private static function icon_globe(): string {
        return '<svg class="egr-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10
                             15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>';
    }
}
