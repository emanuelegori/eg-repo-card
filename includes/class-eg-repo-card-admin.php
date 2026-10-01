<?php
/**
 * EG_Repo_Card_Admin
 * Registers the options page under Settings > EG Repo Card.
 * Handles: GitHub/Forgejo tokens, cache TTL, appearance.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_Admin {

    private const OPTION_GROUP = 'eg_repo_card_options';
    private const PAGE_SLUG    = 'eg-repo-card';
    private const REPO_URL     = 'https://git.emanuelegori.uno/emanuelegori/eg-repo-card';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'add_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register' ] );
        add_action( 'current_screen', [ __CLASS__, 'footer_hooks' ] );
        add_filter(
            'plugin_action_links_' . plugin_basename( EG_REPO_CARD_FILE ),
            [ __CLASS__, 'action_links' ]
        );
    }

    public static function action_links( array $links ): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
            esc_html__( 'Settings', 'eg-repo-card' )
        );
        $docs_link = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url( self::project_urls()['docs'] ),
            esc_html__( 'Documentation', 'eg-repo-card' )
        );
        array_unshift( $links, $docs_link, $settings_link );
        return $links;
    }

    /**
     * Indirizzi del progetto, nella lingua di chi guarda.
     *
     * Stringhe traducibili invece di costanti: una traduzione puo' puntarle
     * alle pagine localizzate, senza controlli sul locale nel codice. In
     * amministrazione conta la lingua scelta dall'utente.
     *
     * @return array<string, string>
     */
    private static function project_urls(): array {
        return [
            /* translators: address of the author's website. Translate with the localized home page, if any; otherwise leave unchanged. */
            'author' => __( 'https://emanuelegori.uno/en/', 'eg-repo-card' ),
            /* translators: address of the plugin page. Translate with the localized page, if any; otherwise leave unchanged. */
            'docs'   => __( 'https://emanuelegori.uno/en/plugins/eg-repo-card/', 'eg-repo-card' ),
            /* translators: address of the page to support the project. Translate with the localized page, if any; otherwise leave unchanged. */
            'donate' => __( 'https://emanuelegori.uno/en/donate/', 'eg-repo-card' ),
            'repo'   => self::REPO_URL,
        ];
    }

    /**
     * Footer only on the plugin settings page, in the native WordPress slots.
     * `update_footer` at priority 20: core_update_footer is already at 10.
     *
     * @param WP_Screen $screen
     */
    public static function footer_hooks( $screen ): void {
        if ( ! isset( $screen->id ) || 'settings_page_' . self::PAGE_SLUG !== $screen->id ) {
            return;
        }
        add_filter( 'admin_footer_text', [ __CLASS__, 'footer_text' ] );
        add_filter( 'update_footer', [ __CLASS__, 'footer_version' ], 20 );
    }

    /**
     * Left side of the footer: author and project links.
     *
     * @param  string $text
     * @return string
     */
    public static function footer_text( $text ): string {
        $urls = self::project_urls();
        $link = static function ( string $url, string $label ): string {
            return sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $url ), esc_html( $label ) );
        };

        $credit = sprintf(
            /* translators: %s: HTML link to the developer's website */
            __( 'Developed with ❤️ and maintained by %s', 'eg-repo-card' ),
            $link( $urls['author'], 'Emanuele Gori' )
        );

        $links = [
            $link( $urls['docs'], __( 'Documentation', 'eg-repo-card' ) ),
            $link( $urls['repo'], __( 'Repository', 'eg-repo-card' ) ),
            $link( $urls['donate'], __( 'Support the project', 'eg-repo-card' ) ),
        ];

        return wp_kses(
            $credit . ' &middot; ' . implode( ' &middot; ', $links ),
            [
                'a' => [
                    'href'   => [],
                    'target' => [],
                    'rel'    => [],
                ],
            ]
        );
    }

    /**
     * Right side of the footer: plugin version and license.
     *
     * @param  string $text
     * @return string
     */
    public static function footer_version( $text ): string {
        $version = sprintf(
            /* translators: %s: plugin version number */
            esc_html__( 'EG Repo Card v%s', 'eg-repo-card' ),
            esc_html( EG_REPO_CARD_VERSION )
        );

        return $version . ' &middot; ' . esc_html__( 'License GPL-2.0-or-later', 'eg-repo-card' );
    }

    public static function add_page(): void {
        add_options_page(
            __( 'EG Repo Card', 'eg-repo-card' ),
            __( 'EG Repo Card', 'eg-repo-card' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register(): void {
        register_setting(
            self::OPTION_GROUP,
            EG_Repo_Card_Settings::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [ 'EG_Repo_Card_Settings', 'sanitize' ],
                'default'           => EG_Repo_Card_Settings::DEFAULTS,
            ]
        );

        foreach ( [ EG_Repo_Card_Settings::GITHUB_TOKEN, EG_Repo_Card_Settings::FORGEJO_TOKEN ] as $option ) {
            register_setting(
                self::OPTION_GROUP,
                $option,
                [
                    'type'              => 'string',
                    'sanitize_callback' => static function ( $value ) use ( $option ) {
                        return EG_Repo_Card_Settings::sanitize_token( $value, $option );
                    },
                ]
            );
        }

        // -- Section: API tokens --
        add_settings_section(
            'eg_repo_card_section_api',
            __( 'API tokens', 'eg-repo-card' ),
            static function () {
                echo '<p class="description">'
                    . esc_html__( 'Optional. Leave a field empty to keep the saved token.', 'eg-repo-card' )
                    . '</p>';
            },
            self::PAGE_SLUG
        );

        self::add_token_field(
            EG_Repo_Card_Settings::GITHUB_TOKEN,
            __( 'GitHub personal access token', 'eg-repo-card' ),
            __( 'Raises the GitHub limit from 60 to 5,000 requests per hour.', 'eg-repo-card' )
        );

        self::add_token_field(
            EG_Repo_Card_Settings::FORGEJO_TOKEN,
            __( 'Forgejo or Gitea token', 'eg-repo-card' ),
            __( 'Needed only for private repositories or instances that require login.', 'eg-repo-card' )
        );

        // -- Section: Cache --
        add_settings_section(
            'eg_repo_card_section_cache',
            __( 'Cache', 'eg-repo-card' ),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'eg_repo_card_cache_hours',
            __( 'Cache duration (hours)', 'eg-repo-card' ),
            static function () {
                printf(
                    '<input type="number" id="eg_repo_card_cache_hours" name="%s[cache_hours]" value="%s" min="1" max="168" class="small-text"><p class="description">%s</p>',
                    esc_attr( EG_Repo_Card_Settings::OPTION ),
                    esc_attr( (string) EG_Repo_Card_Settings::get( 'cache_hours' ) ),
                    esc_html__( 'From 1 to 168 hours. Default: 6', 'eg-repo-card' )
                );
            },
            self::PAGE_SLUG,
            'eg_repo_card_section_cache',
            [ 'label_for' => 'eg_repo_card_cache_hours' ]
        );

        // -- Section: Appearance --
        add_settings_section(
            'eg_repo_card_section_appearance',
            __( 'Appearance', 'eg-repo-card' ),
            static function () {
                echo '<p class="description">'
                    . esc_html__( 'Text and icons switch between light and dark to match the background.', 'eg-repo-card' )
                    . '</p>';
            },
            self::PAGE_SLUG
        );

        self::add_background_field(
            'card_bg',
            __( 'Card background', 'eg-repo-card' ),
            __( 'Neutral preset (light grey)', 'eg-repo-card' )
        );

        self::add_background_field(
            'button_bg',
            __( 'Button background', 'eg-repo-card' ),
            __( 'Neutral preset (grey)', 'eg-repo-card' )
        );

        self::add_checkbox_field( 'border', __( 'Border', 'eg-repo-card' ), __( 'Show a border around the card', 'eg-repo-card' ) );
        self::add_checkbox_field( 'shadow', __( 'Shadow', 'eg-repo-card' ), __( 'Show a shadow under the card', 'eg-repo-card' ) );
        self::add_checkbox_field(
            'avatar',
            __( 'Avatar', 'eg-repo-card' ),
            __( 'Show the owner avatar or the plugin icon', 'eg-repo-card' ),
            __( 'The image is loaded from the platform that hosts it.', 'eg-repo-card' )
        );
    }

    // --- Field helpers ---

    private static function add_token_field( string $option, string $label, string $description ): void {
        add_settings_field(
            $option,
            $label,
            static function () use ( $option, $description ) {
                $has_value = '' !== (string) get_option( $option, '' );
                printf(
                    '<input type="password" id="%1$s" name="%1$s" value="" %2$s class="regular-text" autocomplete="new-password"><p class="description">%3$s</p>',
                    esc_attr( $option ),
                    $has_value ? 'placeholder="••••••••"' : '',
                    esc_html( $description )
                );
            },
            self::PAGE_SLUG,
            'eg_repo_card_section_api',
            [ 'label_for' => $option ]
        );
    }

    private static function add_background_field( string $key, string $label, string $neutral_label ): void {
        add_settings_field(
            'eg_repo_card_' . $key,
            $label,
            static function () use ( $key, $neutral_label ) {
                $settings = EG_Repo_Card_Settings::all();
                $name     = EG_Repo_Card_Settings::OPTION . '[' . $key . ']';
                $choices  = [
                    'neutral' => $neutral_label,
                    'none'    => __( 'Transparent', 'eg-repo-card' ),
                    'auto'    => __( 'Follow the visitor browser', 'eg-repo-card' ),
                    'custom'  => __( 'Custom color', 'eg-repo-card' ),
                ];

                printf( '<select id="eg_repo_card_%s" name="%s">', esc_attr( $key ), esc_attr( $name ) );
                foreach ( $choices as $value => $text ) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr( $value ),
                        selected( $settings[ $key ], $value, false ),
                        esc_html( $text )
                    );
                }
                echo '</select> ';

                printf(
                    '<input type="color" name="%s" value="%s" aria-label="%s">',
                    esc_attr( EG_Repo_Card_Settings::OPTION . '[' . $key . '_color]' ),
                    esc_attr( $settings[ $key . '_color' ] ),
                    esc_attr__( 'Custom color', 'eg-repo-card' )
                );

                echo '<p class="description">'
                    . esc_html__( '"Follow the visitor browser" switches between light and dark with the system theme. The color picker applies only to "Custom color".', 'eg-repo-card' )
                    . '</p>';
            },
            self::PAGE_SLUG,
            'eg_repo_card_section_appearance',
            [ 'label_for' => 'eg_repo_card_' . $key ]
        );
    }

    private static function add_checkbox_field( string $key, string $label, string $text, string $description = '' ): void {
        add_settings_field(
            'eg_repo_card_' . $key,
            $label,
            static function () use ( $key, $text, $description ) {
                printf(
                    '<label><input type="checkbox" name="%s" value="1"%s> %s</label>',
                    esc_attr( EG_Repo_Card_Settings::OPTION . '[' . $key . ']' ),
                    checked( (int) EG_Repo_Card_Settings::get( $key ), 1, false ),
                    esc_html( $text )
                );
                if ( '' !== $description ) {
                    echo '<p class="description">' . esc_html( $description ) . '</p>';
                }
            },
            self::PAGE_SLUG,
            'eg_repo_card_section_appearance'
        );
    }

    // --- Admin page render ---

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $tokens = [
            'github'  => [ EG_Repo_Card_Settings::GITHUB_TOKEN, 'GitHub' ],
            'forgejo' => [ EG_Repo_Card_Settings::FORGEJO_TOKEN, 'Forgejo / Gitea' ],
        ];

        // Handle token deletion
        foreach ( $tokens as $platform => [ $option, $label ] ) {
            $action = 'eg_repo_card_delete_' . $platform . '_token';
            if ( isset( $_POST[ $action ] ) && check_admin_referer( $action ) ) {
                delete_option( $option );
                echo '<div class="notice notice-success is-dismissible"><p>'
                    . esc_html(
                        sprintf(
                            /* translators: %s: platform name (GitHub or Forgejo / Gitea) */
                            __( '%s token removed.', 'eg-repo-card' ),
                            $label
                        )
                    )
                    . '</p></div>';
            }
        }

        // Handle manual cache flush
        if ( isset( $_POST['eg_repo_card_flush_cache'] ) && check_admin_referer( 'eg_repo_card_flush_cache' ) ) {
            EG_Repo_Card_API::flush_cache();
            echo '<div class="notice notice-success is-dismissible"><p>'
                . esc_html__( 'Cache cleared.', 'eg-repo-card' )
                . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'EG Repo Card', 'eg-repo-card' ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::PAGE_SLUG );
                submit_button( __( 'Save settings', 'eg-repo-card' ) );
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Flush cache', 'eg-repo-card' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Cards load fresh data from the repositories on the next page view.', 'eg-repo-card' ); ?>
            </p>
            <form method="post">
                <?php wp_nonce_field( 'eg_repo_card_flush_cache' ); ?>
                <?php submit_button( __( 'Flush cache now', 'eg-repo-card' ), 'secondary', 'eg_repo_card_flush_cache', false ); ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Remove API tokens', 'eg-repo-card' ); ?></h2>
            <?php
            $has_any_token = false;
            foreach ( $tokens as $platform => [ $option, $label ] ) :
                if ( '' === (string) get_option( $option, '' ) ) {
                    continue;
                }
                $has_any_token = true;
                $action        = 'eg_repo_card_delete_' . $platform . '_token';
                ?>
                <form method="post" style="display:inline-block;margin-right:8px;">
                    <?php wp_nonce_field( $action ); ?>
                    <?php
                    submit_button(
                        sprintf(
                            /* translators: %s: platform name (GitHub or Forgejo / Gitea) */
                            __( 'Remove %s token', 'eg-repo-card' ),
                            $label
                        ),
                        'delete',
                        $action,
                        false
                    );
                    ?>
                </form>
                <?php
            endforeach;
            if ( ! $has_any_token ) :
                ?>
                <p class="description"><?php esc_html_e( 'No token saved.', 'eg-repo-card' ); ?></p>
                <?php
            endif;
            ?>

            <hr>

            <h2><?php esc_html_e( 'Shortcode usage', 'eg-repo-card' ); ?></h2>
            <p><code>[eg-repo-card url="https://github.com/owner/repo"]</code></p>
            <p><code>[eg-repo-card url="https://codeberg.org/owner/repo"]</code></p>
            <p><code>[eg-repo-card url="https://wordpress.org/plugins/plugin-slug/"]</code></p>
            <p class="description">
                <?php esc_html_e( 'Works with GitHub, Codeberg, any Forgejo or Gitea instance and the WordPress.org plugin directory.', 'eg-repo-card' ); ?>
            </p>
        </div>
        <?php
    }
}
