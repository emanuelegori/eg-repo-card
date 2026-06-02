<?php
/**
 * EGR_Settings
 * Registers the plugin options page under Settings > EG Ranking Repo.
 * Handles: GitHub/Forgejo tokens, card colour scheme, cache TTL.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EGR_Settings {

    private const OPTION_GROUP = 'egr_options';
    private const PAGE_SLUG    = 'eg-ranking-repo';

    /** Default values for all options */
    private const DEFAULTS = [
        'egr_github_token'  => '',
        'egr_forgejo_token' => '',
        'egr_cache_hours'   => 6,
        'egr_card_bg_color' => '#f8f8f8',
        'egr_card_txt_color' => '#111111',
        'egr_btn_bg_color'  => '#e0e0e0',
        'egr_btn_txt_color' => '#111111',
    ];

    public static function init(): void {
        add_action( 'admin_menu',  [ __CLASS__, 'add_page' ] );
        add_action( 'admin_init',  [ __CLASS__, 'register' ] );
        add_filter(
            'plugin_action_links_' . plugin_basename( EGR_PLUGIN_FILE ),
            [ __CLASS__, 'action_links' ]
        );
    }

    public static function action_links( array $links ): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
            esc_html__( 'Settings', 'eg-ranking-repo' )
        );
        $docs_link = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url( 'https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo' ),
            esc_html__( 'Documentation', 'eg-ranking-repo' )
        );
        array_unshift( $links, $docs_link, $settings_link );
        return $links;
    }

    public static function add_page(): void {
        add_options_page(
            __( 'EG Ranking Repo',          'eg-ranking-repo' ),
            __( 'EG Ranking Repo',          'eg-ranking-repo' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register(): void {
        // Sanitise callbacks keyed by option type
        $schema = [
            'egr_github_token'   => 'token',
            'egr_forgejo_token'  => 'token',
            'egr_cache_hours'    => 'hours',
            'egr_card_bg_color'  => 'color',
            'egr_card_txt_color' => 'color',
            'egr_btn_bg_color'   => 'color',
            'egr_btn_txt_color'  => 'color',
        ];

        foreach ( $schema as $key => $type ) {
            $default = self::DEFAULTS[ $key ];
            register_setting(
                self::OPTION_GROUP,
                $key,
                [
                    'sanitize_callback' => static function ( $val ) use ( $type, $default, $key ) {
                        return self::sanitize( $val, $type, $default, $key );
                    },
                ]
            );
        }

        // -- Section: API Tokens --
        add_settings_section(
            'egr_sec_api',
            __( 'API Tokens', 'eg-ranking-repo' ),
            static function () {
                echo '<p class="description">'
                    . esc_html__( 'Optional tokens to increase API rate limits.', 'eg-ranking-repo' )
                    . '</p>';
            },
            self::PAGE_SLUG
        );

        self::add_field(
            'egr_github_token',
            __( 'GitHub Personal Access Token', 'eg-ranking-repo' ),
            'egr_sec_api',
            'password',
            __( 'Without token: 60 requests/hour. With token: 5,000 requests/hour.', 'eg-ranking-repo' )
        );

        self::add_field(
            'egr_forgejo_token',
            __( 'Forgejo API Token (optional)', 'eg-ranking-repo' ),
            'egr_sec_api',
            'password',
            __( 'Required only for private repositories or instances with mandatory authentication.', 'eg-ranking-repo' )
        );

        // -- Section: Cache --
        add_settings_section(
            'egr_sec_cache',
            __( 'Cache', 'eg-ranking-repo' ),
            static function () {
                echo '<p class="description">'
                    . esc_html__( 'Repository data is cached to reduce API calls.', 'eg-ranking-repo' )
                    . '</p>';
            },
            self::PAGE_SLUG
        );

        add_settings_field(
            'egr_cache_hours',
            __( 'Cache duration (hours)', 'eg-ranking-repo' ),
            static function () {
                $val = (int) get_option( 'egr_cache_hours', 6 );
                printf(
                    '<input type="number" id="egr_cache_hours" name="egr_cache_hours"
                            value="%s" min="1" max="168" class="small-text">
                     <p class="description">%s</p>',
                    esc_attr( (string) $val ),
                    esc_html__( 'Recommended values: 6–24 hours. Maximum 168 (1 week).', 'eg-ranking-repo' )
                );
            },
            self::PAGE_SLUG,
            'egr_sec_cache'
        );

        // -- Section: Stile card --
        add_settings_section(
            'egr_sec_style',
            __( 'Card style', 'eg-ranking-repo' ),
            static function () {
                echo '<p class="description">'
                    . esc_html__( 'Customise the card colours. The default values ensure maximum readability.', 'eg-ranking-repo' )
                    . '</p>';
            },
            self::PAGE_SLUG
        );

        self::add_color_field(
            'egr_card_bg_color',
            __( 'Card background colour', 'eg-ranking-repo' ),
            self::DEFAULTS['egr_card_bg_color']
        );

        self::add_color_field(
            'egr_card_txt_color',
            __( 'Card text colour', 'eg-ranking-repo' ),
            self::DEFAULTS['egr_card_txt_color']
        );

        self::add_color_field(
            'egr_btn_bg_color',
            __( 'Button background colour', 'eg-ranking-repo' ),
            self::DEFAULTS['egr_btn_bg_color']
        );

        self::add_color_field(
            'egr_btn_txt_color',
            __( 'Button text colour', 'eg-ranking-repo' ),
            self::DEFAULTS['egr_btn_txt_color']
        );
    }

    // --- Helper: add a text/password field ---

    private static function add_field(
        string $key,
        string $label,
        string $section,
        string $type = 'text',
        string $description = ''
    ): void {
        add_settings_field(
            $key,
            $label,
            static function () use ( $key, $type, $description ) {
                $has_value = ( 'password' === $type ) && ! empty( get_option( $key, '' ) );
                printf(
                    '<input type="%s" id="%s" name="%s" value="" %s class="regular-text" autocomplete="new-password">',
                    esc_attr( $type ),
                    esc_attr( $key ),
                    esc_attr( $key ),
                    $has_value ? 'placeholder="••••••••"' : ''
                );
                if ( $description ) {
                    echo '<p class="description">' . esc_html( $description ) . '</p>';
                }
            },
            self::PAGE_SLUG,
            $section
        );
    }

    // --- Helper: add a color picker field ---

    private static function add_color_field(
        string $key,
        string $label,
        string $default
    ): void {
        add_settings_field(
            $key,
            $label,
            static function () use ( $key, $default ) {
                $val = get_option( $key, $default );
                printf(
                    '<input type="color" id="%s" name="%s" value="%s">
                     <code style="margin-left:6px;vertical-align:middle;">%s</code>',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_attr( $val ),
                    esc_html( $val )
                );
            },
            self::PAGE_SLUG,
            'egr_sec_style'
        );
    }

    // --- Sanitise helper ---

    private static function sanitize( $val, string $type, $default, string $key = '' ) {
        switch ( $type ) {
            case 'token':
                $new = sanitize_text_field( $val );
                // If the field was left blank (placeholder shown), keep the stored value
                if ( '' === $new && $key ) {
                    return get_option( $key, '' );
                }
                return $new;
            case 'hours':
                return max( 1, min( 168, (int) $val ) );
            case 'color':
                return sanitize_hex_color( $val ) ?: $default;
            default:
                return sanitize_text_field( $val );
        }
    }

    // --- Admin page render ---

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Handle token deletion
        foreach ( [ 'github' => 'egr_github_token', 'forgejo' => 'egr_forgejo_token' ] as $platform => $option_key ) {
            $post_key  = 'egr_delete_' . $platform . '_token';
            $nonce_key = $post_key . '_nonce';
            if ( isset( $_POST[ $post_key ] ) && check_admin_referer( $nonce_key ) ) {
                delete_option( $option_key );
                $label = ( 'github' === $platform ) ? 'GitHub' : 'Forgejo';
                /* translators: %s: nome piattaforma (GitHub o Forgejo) */
                $notice = sprintf( esc_html__( 'Token %s removed.', 'eg-ranking-repo' ), esc_html( $label ) );
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $notice già costruita con esc_html__() e esc_html()
                echo '<div class="notice notice-success is-dismissible"><p>' . $notice . '</p></div>';
            }
        }

        // Handle manual cache flush
        if (
            isset( $_POST['egr_flush_cache'] )
            && check_admin_referer( 'egr_flush_cache_nonce' )
        ) {
            self::flush_all_cache();
            echo '<div class="notice notice-success is-dismissible"><p>'
                . esc_html__( 'Cache cleared.', 'eg-ranking-repo' )
                . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'EG Ranking Repo — Settings', 'eg-ranking-repo' ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::PAGE_SLUG );
                submit_button( __( 'Save settings', 'eg-ranking-repo' ) );
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Flush cache', 'eg-ranking-repo' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Forces a fresh data fetch from repositories on next page view.', 'eg-ranking-repo' ); ?>
            </p>
            <form method="post">
                <?php wp_nonce_field( 'egr_flush_cache_nonce' ); ?>
                <?php submit_button(
                    __( 'Flush cache now', 'eg-ranking-repo' ),
                    'secondary',
                    'egr_flush_cache',
                    false
                ); ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Remove API tokens', 'eg-ranking-repo' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Use these buttons to delete a saved token (e.g. if it has been compromised).', 'eg-ranking-repo' ); ?>
            </p>
            <?php
            $has_any_token = false;
            foreach ( [ 'github' => 'egr_github_token', 'forgejo' => 'egr_forgejo_token' ] as $platform => $option_key ) :
                if ( ! empty( get_option( $option_key, '' ) ) ) :
                    $has_any_token = true;
                    $label = ( 'github' === $platform ) ? 'GitHub' : 'Forgejo';
                    ?>
                    <form method="post" style="display:inline-block;margin-right:8px;">
                        <?php wp_nonce_field( 'egr_delete_' . $platform . '_token_nonce' ); ?>
                        <?php submit_button(
                            /* translators: %s: platform name (GitHub or Forgejo) */
                            sprintf( __( 'Remove %s token', 'eg-ranking-repo' ), $label ),
                            'delete',
                            'egr_delete_' . $platform . '_token',
                            false
                        ); ?>
                    </form>
                    <?php
                endif;
            endforeach;
            if ( ! $has_any_token ) :
                ?>
                <p class="description"><?php esc_html_e( 'No token saved.', 'eg-ranking-repo' ); ?></p>
                <?php
            endif;
            ?>

            <hr>

            <h2><?php esc_html_e( 'Shortcode usage', 'eg-ranking-repo' ); ?></h2>
            <table class="widefat" style="max-width:600px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Platform', 'eg-ranking-repo' ); ?></th>
                        <th><?php esc_html_e( 'Shortcode example', 'eg-ranking-repo' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>GitHub</td>
                        <td><code>[eg-ranking-repo url="https://github.com/owner/repo"]</code></td>
                    </tr>
                    <tr>
                        <td>Forgejo / Gitea</td>
                        <td><code>[eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]</code></td>
                    </tr>
                </tbody>
            </table>
            <p class="description" style="margin-top:8px;">
                <?php esc_html_e( 'Works with any self-hosted Forgejo or Gitea instance.', 'eg-ranking-repo' ); ?>
            </p>

            <hr>
            <p class="description">
                <?php
                printf(
                    /* translators: %s: plugin version number */
                    esc_html__( 'Plugin version: %s', 'eg-ranking-repo' ),
                    '<strong>' . esc_html( EGR_VERSION ) . '</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Delete all transients created by EGR_API.
     * Pattern: transient keys start with "egr_" followed by 32 hex chars (md5).
     */
    private static function flush_all_cache(): void {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk DELETE di transient per pattern, non cachabile
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                    OR option_name LIKE %s",
                $wpdb->esc_like( '_transient_egr_' ) . '%',
                $wpdb->esc_like( '_transient_timeout_egr_' ) . '%'
            )
        );
        // Svuota anche l'object cache (Redis/Memcached) se presente,
        // altrimenti get_transient() continuerebbe a restituire i valori vecchi.
        wp_cache_flush();
    }
}
