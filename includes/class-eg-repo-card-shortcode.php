<?php
/**
 * EG_Repo_Card_Shortcode
 * Registers and renders the [eg-repo-card url="..."] shortcode.
 * [eg-ranking-repo] is kept as a deprecated alias.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Shortcode {

    public const TAG = 'eg-repo-card';

    /** Shortcode of EG Ranking Repo 1.x, still rendered for existing content */
    public const LEGACY_TAG = 'eg-ranking-repo';

    /** Language colours (GitHub Linguist), grey for anything else */
    private const LANGUAGE_COLORS = [
        'c'                => '#555555',
        'c#'               => '#178600',
        'c++'              => '#f34b7d',
        'css'              => '#663399',
        'dart'             => '#00b4ab',
        'dockerfile'       => '#384d54',
        'elixir'           => '#6e4a7e',
        'go'               => '#00add8',
        'haskell'          => '#5e5086',
        'html'             => '#e34c26',
        'java'             => '#b07219',
        'javascript'       => '#f1e05a',
        'jupyter notebook' => '#da5b0b',
        'kotlin'           => '#a97bff',
        'lua'              => '#000080',
        'makefile'         => '#427819',
        'nix'              => '#7e7eff',
        'php'              => '#4f5d95',
        'python'           => '#3572a5',
        'ruby'             => '#701516',
        'rust'             => '#dea584',
        'scss'             => '#c6538c',
        'shell'            => '#89e051',
        'svelte'           => '#ff3e00',
        'swift'            => '#f05138',
        'typescript'       => '#3178c6',
        'vue'              => '#41b883',
        'zig'              => '#ec915c',
    ];

    /** Inline CSS is attached once per request */
    private static bool $style_added = false;

    public static function register(): void {
        add_action( 'init', static function () {
            add_shortcode( self::TAG, [ __CLASS__, 'render' ] );

            // Il vecchio plugin, se ancora attivo, registra lo stesso tag: non sovrascriverlo.
            if ( ! shortcode_exists( self::LEGACY_TAG ) ) {
                add_shortcode( self::LEGACY_TAG, [ __CLASS__, 'render' ] );
            }
        } );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_style' ] );
    }

    /**
     * Register the stylesheet (enqueued only when the shortcode is used).
     */
    public static function register_style(): void {
        wp_register_style(
            'eg-repo-card',
            EG_REPO_CARD_URL . 'assets/css/eg-repo-card.css',
            [],
            EG_REPO_CARD_VERSION
        );
    }

    /**
     * Render the shortcode output.
     *
     * @param  array|string $atts  Shortcode attributes.
     * @param  string|null  $content
     * @param  string       $tag   Shortcode tag in use.
     * @return string       HTML markup.
     */
    public static function render( $atts, $content = null, $tag = self::TAG ): string {
        $atts = shortcode_atts( [ 'url' => '' ], $atts, $tag ? $tag : self::TAG );

        if ( '' === trim( (string) $atts['url'] ) ) {
            return self::error( __( 'Missing url attribute in the shortcode.', 'eg-repo-card' ) );
        }

        $data = EG_Repo_Card_API::fetch( (string) $atts['url'] );

        if ( is_wp_error( $data ) ) {
            return self::error( $data->get_error_message() );
        }

        self::enqueue_style();

        $has_homepage = ! empty( $data['homepage'] );
        $show_avatar  = EG_Repo_Card_Settings::get( 'avatar' ) && ! empty( $data['avatar'] );
        $platform     = (string) ( $data['platform'] ?? 'forgejo' );
        $stars_raw    = number_format_i18n( (int) $data['stars'] );
        $stars_title  = sprintf(
            /* translators: %s: formatted star count */
            _n( '%s star', '%s stars', (int) $data['stars'], 'eg-repo-card' ),
            $stars_raw
        );

        $updated     = EG_Repo_Card_API::timestamp( (string) $data['updated_at'] );
        $date_label  = $updated ? wp_date( 'd-m-Y', $updated ) : __( 'N/A', 'eg-repo-card' );
        $date_title  = $updated
            ? sprintf(
                /* translators: %s: time since the last update, e.g. "3 days" */
                __( 'Last updated %s ago', 'eg-repo-card' ),
                human_time_diff( $updated )
            )
            : __( 'Last updated', 'eg-repo-card' );

        $version       = (string) ( $data['version'] ?? '' );
        $language      = (string) ( $data['language'] ?? '' );
        $license       = (string) ( $data['license'] ?? '' );
        $download_url  = (string) ( $data['download_url'] ?? '' );
        $download_type = (string) ( $data['download_type'] ?? '' );

        ob_start();
        ?>
        <div class="eg-repo-card eg-repo-card--<?php echo esc_attr( $platform ); ?>">

            <div class="eg-repo-card__header">
                <?php if ( $show_avatar ) : ?>
                <img class="eg-repo-card__avatar" src="<?php echo esc_url( $data['avatar'] ); ?>" alt="" width="28" height="28" loading="lazy" decoding="async">
                <?php endif; ?>
                <span class="eg-repo-card__name"><?php echo esc_html( $data['full_name'] ); ?></span>
                <span class="eg-repo-card__platform eg-repo-card__platform--<?php echo esc_attr( $platform ); ?>">
                    <?php echo self::platform_icon( $platform ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php echo esc_html( $data['platform_label'] ); ?>
                </span>
                <?php if ( ! empty( $data['archived'] ) ) : ?>
                <span class="eg-repo-card__archived" title="<?php esc_attr_e( 'This repository is read-only', 'eg-repo-card' ); ?>">
                    <?php esc_html_e( 'Archived', 'eg-repo-card' ); ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if ( '' !== $data['description'] ) : ?>
            <p class="eg-repo-card__description"><?php echo esc_html( $data['description'] ); ?></p>
            <?php endif; ?>

            <div class="eg-repo-card__actions">

                <?php if ( $has_homepage ) : ?>
                <a href="<?php echo esc_url( $data['homepage'] ); ?>"
                   class="eg-repo-card__btn"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php esc_attr_e( 'Project website', 'eg-repo-card' ); ?>">
                    <?php echo self::icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php esc_html_e( 'Website', 'eg-repo-card' ); ?>
                </a>
                <?php else : ?>
                <span class="eg-repo-card__btn eg-repo-card__btn--disabled"
                      aria-disabled="true"
                      title="<?php esc_attr_e( 'No website set in the repository', 'eg-repo-card' ); ?>">
                    <?php echo self::icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php esc_html_e( 'No website', 'eg-repo-card' ); ?>
                </span>
                <?php endif; ?>

                <a href="<?php echo esc_url( $data['repo_url'] ); ?>"
                   class="eg-repo-card__btn"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php esc_attr_e( 'Go to repository', 'eg-repo-card' ); ?>">
                    <?php echo self::icon( 'code' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php esc_html_e( 'Source Code', 'eg-repo-card' ); ?>
                </a>

                <?php if ( '' !== $download_url ) : ?>
                <a href="<?php echo esc_url( $download_url ); ?>"
                   class="eg-repo-card__btn eg-repo-card__btn--download"
                   <?php echo ( 'page' === $download_type ) ? 'target="_blank" rel="noopener noreferrer"' : 'rel="nofollow"'; ?>
                   title="<?php echo esc_attr( 'file' === $download_type ? __( 'Download the latest release', 'eg-repo-card' ) : __( 'Open the latest release page', 'eg-repo-card' ) ); ?>">
                    <?php echo self::icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php esc_html_e( 'Download', 'eg-repo-card' ); ?>
                </a>
                <?php endif; ?>

                <span class="eg-repo-card__btn eg-repo-card__btn--badge"
                      title="<?php echo esc_attr( $date_title ); ?>">
                    <?php echo self::icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php echo esc_html( $date_label ); ?>
                </span>

                <?php if ( '' !== $version ) : ?>
                <span class="eg-repo-card__btn eg-repo-card__btn--badge"
                      title="<?php esc_attr_e( 'Latest release', 'eg-repo-card' ); ?>">
                    <?php echo self::icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php echo esc_html( $version ); ?>
                </span>
                <?php endif; ?>

                <?php if ( '' !== $language ) : ?>
                <span class="eg-repo-card__btn eg-repo-card__btn--badge"
                      title="<?php esc_attr_e( 'Main language', 'eg-repo-card' ); ?>">
                    <span class="eg-repo-card__dot" style="background-color:<?php echo esc_attr( self::language_color( $language ) ); ?>" aria-hidden="true"></span>
                    <?php echo esc_html( $language ); ?>
                </span>
                <?php endif; ?>

                <?php if ( '' !== $license ) : ?>
                <span class="eg-repo-card__btn eg-repo-card__btn--badge"
                      title="<?php esc_attr_e( 'License', 'eg-repo-card' ); ?>">
                    <?php echo self::icon( 'scale' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php echo esc_html( $license ); ?>
                </span>
                <?php endif; ?>

                <span class="eg-repo-card__btn eg-repo-card__btn--badge"
                      title="<?php echo esc_attr( $stars_title ); ?>">
                    <?php echo self::icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG ?>
                    <?php echo esc_html( EG_Repo_Card_API::format_stars( (int) $data['stars'] ) ); ?>
                </span>

            </div>

        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Error output: visible to editors only, nothing for visitors.
     *
     * @param  string $message
     * @return string
     */
    private static function error( string $message ): string {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return '';
        }

        return sprintf(
            '<p class="eg-repo-card-error">%s: %s</p>',
            esc_html__( 'EG Repo Card error', 'eg-repo-card' ),
            esc_html( $message )
        );
    }

    /**
     * Enqueue the stylesheet plus the CSS of the appearance settings.
     */
    private static function enqueue_style(): void {
        if ( ! wp_style_is( 'eg-repo-card', 'registered' ) ) {
            self::register_style();
        }
        wp_enqueue_style( 'eg-repo-card' );

        if ( ! self::$style_added ) {
            wp_add_inline_style( 'eg-repo-card', EG_Repo_Card_Style::css() );
            self::$style_added = true;
        }
    }

    /**
     * Colour of the language dot.
     *
     * @param  string $language
     * @return string
     */
    private static function language_color( string $language ): string {
        $colors = apply_filters( 'eg_repo_card_language_colors', self::LANGUAGE_COLORS );
        $color  = $colors[ strtolower( $language ) ] ?? '#8b949e';
        $color  = sanitize_hex_color( (string) $color );
        return $color ? $color : '#8b949e';
    }

    // --- Inline SVG icons (no external font/CDN dependency) ---

    /**
     * Platform logos from Simple Icons (CC0-1.0).
     *
     * @param  string $platform
     * @return string
     */
    private static function platform_icon( string $platform ): string {
        $paths = [
            'github'   => 'M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12',
            'forgejo'  => 'M16.7773 0c1.6018 0 2.9004 1.2986 2.9004 2.9005s-1.2986 2.9004-2.9004 2.9004c-1.0854 0-2.0315-.596-2.5288-1.4787H12.91c-2.3322 0-4.2272 1.8718-4.2649 4.195l-.0007 2.1175a7.0759 7.0759 0 0 1 4.148-1.4205l.1176-.001 1.3385.0002c.4973-.8827 1.4434-1.4788 2.5288-1.4788 1.6018 0 2.9004 1.2986 2.9004 2.9005s-1.2986 2.9004-2.9004 2.9004c-1.0854 0-2.0315-.596-2.5288-1.4787H12.91c-2.3322 0-4.2272 1.8718-4.2649 4.195l-.0007 2.319c.8827.4973 1.4788 1.4434 1.4788 2.5287 0 1.602-1.2986 2.9005-2.9005 2.9005-1.6018 0-2.9004-1.2986-2.9004-2.9005 0-1.0853.596-2.0314 1.4788-2.5287l-.0002-9.9831c0-3.887 3.1195-7.0453 6.9915-7.108l.1176-.001h1.3385C14.7458.5962 15.692 0 16.7773 0ZM7.2227 19.9052c-.6596 0-1.1943.5347-1.1943 1.1943s.5347 1.1943 1.1943 1.1943 1.1944-.5347 1.1944-1.1943-.5348-1.1943-1.1944-1.1943Zm9.5546-10.4644c-.6596 0-1.1944.5347-1.1944 1.1943s.5348 1.1943 1.1944 1.1943c.6596 0 1.1943-.5347 1.1943-1.1943s-.5347-1.1943-1.1943-1.1943Zm0-7.7346c-.6596 0-1.1944.5347-1.1944 1.1943s.5348 1.1943 1.1944 1.1943c.6596 0 1.1943-.5347 1.1943-1.1943s-.5347-1.1943-1.1943-1.1943Z',
            'codeberg' => 'M11.999.747A11.974 11.974 0 0 0 0 12.75c0 2.254.635 4.465 1.833 6.376L11.837 6.19c.072-.092.251-.092.323 0l4.178 5.402h-2.992l.065.239h3.113l.882 1.138h-3.674l.103.374h3.86l.777 1.003h-4.358l.135.483h4.593l.695.894h-5.038l.165.589h5.326l.609.785h-5.717l.182.65h6.038l.562.727h-6.397l.183.65h6.717A12.003 12.003 0 0 0 24 12.75 11.977 11.977 0 0 0 11.999.747zm3.654 19.104.182.65h5.326c.173-.204.353-.433.513-.65zm.385 1.377.18.65h3.563c.233-.198.485-.428.712-.65zm.383 1.377.182.648h1.203c.356-.204.685-.412 1.042-.648zz',
            'gitea'    => 'M4.209 4.603c-.247 0-.525.02-.84.088-.333.07-1.28.283-2.054 1.027C-.403 7.25.035 9.685.089 10.052c.065.446.263 1.687 1.21 2.768 1.749 2.141 5.513 2.092 5.513 2.092s.462 1.103 1.168 2.119c.955 1.263 1.936 2.248 2.89 2.367 2.406 0 7.212-.004 7.212-.004s.458.004 1.08-.394c.535-.324 1.013-.893 1.013-.893s.492-.527 1.18-1.73c.21-.37.385-.729.538-1.068 0 0 2.107-4.471 2.107-8.823-.042-1.318-.367-1.55-.443-1.627-.156-.156-.366-.153-.366-.153s-4.475.252-6.792.306c-.508.011-1.012.023-1.512.027v4.474l-.634-.301c0-1.39-.004-4.17-.004-4.17-1.107.016-3.405-.084-3.405-.084s-5.399-.27-5.987-.324c-.187-.011-.401-.032-.648-.032zm.354 1.832h.111s.271 2.269.6 3.597C5.549 11.147 6.22 13 6.22 13s-.996-.119-1.641-.348c-.99-.324-1.409-.714-1.409-.714s-.73-.511-1.096-1.52C1.444 8.73 2.021 7.7 2.021 7.7s.32-.859 1.47-1.145c.395-.106.863-.12 1.072-.12zm8.33 2.554c.26.003.509.127.509.127l.868.422-.529 1.075a.686.686 0 0 0-.614.359.685.685 0 0 0 .072.756l-.939 1.924a.69.69 0 0 0-.66.527.687.687 0 0 0 .347.763.686.686 0 0 0 .867-.206.688.688 0 0 0-.069-.882l.916-1.874a.667.667 0 0 0 .237-.02.657.657 0 0 0 .271-.137 8.826 8.826 0 0 1 1.016.512.761.761 0 0 1 .286.282c.073.21-.073.569-.073.569-.087.29-.702 1.55-.702 1.55a.692.692 0 0 0-.676.477.681.681 0 1 0 1.157-.252c.073-.141.141-.282.214-.431.19-.397.515-1.16.515-1.16.035-.066.218-.394.103-.814-.095-.435-.48-.638-.48-.638-.467-.301-1.116-.58-1.116-.58s0-.156-.042-.27a.688.688 0 0 0-.148-.241l.516-1.062 2.89 1.401s.48.218.583.619c.073.282-.019.534-.069.657-.24.587-2.1 4.317-2.1 4.317s-.232.554-.748.588a1.065 1.065 0 0 1-.393-.045l-.202-.08-4.31-2.1s-.417-.218-.49-.596c-.083-.31.104-.691.104-.691l2.073-4.272s.183-.37.466-.497a.855.855 0 0 1 .35-.077z',
        ];

        if ( ! isset( $paths[ $platform ] ) ) {
            return '';
        }

        return '<svg class="eg-repo-card__icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="' . $paths[ $platform ] . '"/></svg>';
    }

    /**
     * Stroke icons.
     *
     * @param  string $name
     * @return string
     */
    private static function icon( string $name ): string {
        $shapes = [
            'star'     => '<path fill="currentColor" stroke="none" d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'code'     => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
            'tag'      => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
            'globe'    => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
            'scale'    => '<path d="M12 3v18"/><path d="M5 21h14"/><path d="M3 7h18"/><path d="M6 7l-3 7a3 3 0 0 0 6 0z"/><path d="M18 7l-3 7a3 3 0 0 0 6 0z"/>',
        ];

        if ( ! isset( $shapes[ $name ] ) ) {
            return '';
        }

        return '<svg class="eg-repo-card__icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $shapes[ $name ] . '</svg>';
    }
}
