<?php
/**
 * EG_Repo_Card_API
 * Handles all remote API calls (GitHub REST API, Forgejo/Gitea API v1).
 * Results are cached via WordPress transients.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EG_Repo_Card_API {

    /** Bump when the shape of the cached array changes */
    private const SCHEMA = 2;

    /** How long the last good result is kept as a fallback for API failures */
    private const LAST_GOOD_TTL = 30 * DAY_IN_SECONDS;

    /** How long a failed request is remembered before trying again */
    private const ERROR_TTL = 5 * MINUTE_IN_SECONDS;

    /**
     * Fetch repository data from GitHub or any Forgejo/Gitea instance.
     *
     * Se l'API fallisce ma esiste un risultato valido precedente, restituisce
     * quello: meglio un dato di qualche ora fa che una card rotta.
     *
     * @param  string $url  Full repository URL, e.g. https://github.com/owner/repo
     * @return array|WP_Error  Normalised repo data array on success, WP_Error on failure.
     */
    public static function fetch( string $url ): array|WP_Error {
        $target = self::parse_url( $url );
        if ( is_wp_error( $target ) ) {
            return $target;
        }

        $hash      = md5( self::SCHEMA . '|' . $target['host'] . '/' . $target['owner'] . '/' . $target['repo'] );
        $gen       = (int) get_option( EG_Repo_Card_Settings::CACHE_GEN, 1 );
        $fresh_key = 'eg_repo_card_data_' . $gen . '_' . $hash;
        $last_key  = 'eg_repo_card_last_' . $hash;

        $cached = get_transient( $fresh_key );
        if ( is_array( $cached ) ) {
            if ( isset( $cached['error'] ) ) {
                $last = get_transient( $last_key );
                return is_array( $last ) ? $last : new WP_Error( 'eg_repo_card_api_error', (string) $cached['error'] );
            }
            return $cached;
        }

        $result = self::fetch_remote( $target );

        if ( is_wp_error( $result ) ) {
            set_transient( $fresh_key, [ 'error' => $result->get_error_message() ], self::ERROR_TTL );
            $last = get_transient( $last_key );
            return is_array( $last ) ? $last : $result;
        }

        $hours = (int) EG_Repo_Card_Settings::get( 'cache_hours' );
        set_transient( $fresh_key, $result, max( 1, $hours ) * HOUR_IN_SECONDS );
        set_transient( $last_key, $result, self::LAST_GOOD_TTL );

        return $result;
    }

    /**
     * Split a repository URL into host, owner and repository name.
     *
     * @param  string $url
     * @return array|WP_Error
     */
    private static function parse_url( string $url ): array|WP_Error {
        $url    = trim( $url );
        $parsed = wp_parse_url( $url );

        if (
            ! $parsed
            || empty( $parsed['host'] )
            || empty( $parsed['path'] )
            || ! in_array( strtolower( $parsed['scheme'] ?? '' ), [ 'http', 'https' ], true )
        ) {
            return new WP_Error(
                'eg_repo_card_invalid_url',
                __( 'Invalid repository URL.', 'eg-repo-card' )
            );
        }

        $host = strtolower( $parsed['host'] );
        if ( ! empty( $parsed['port'] ) ) {
            $host .= ':' . (int) $parsed['port'];
        }

        $parts = array_values( array_filter( explode( '/', trim( $parsed['path'], '/' ) ) ) );
        if ( count( $parts ) < 2 ) {
            return new WP_Error(
                'eg_repo_card_invalid_url',
                __( 'Invalid repository URL: missing owner/repo in path.', 'eg-repo-card' )
            );
        }

        $repo = preg_replace( '/\.git$/', '', $parts[1] );

        return [
            'url'   => rtrim( $url, '/' ),
            'host'  => $host,
            'owner' => rawurlencode( rawurldecode( $parts[0] ) ),
            'repo'  => rawurlencode( rawurldecode( $repo ) ),
        ];
    }

    /**
     * Query the APIs and build the normalised data array.
     *
     * @param  array $target  Output of parse_url().
     * @return array|WP_Error
     */
    private static function fetch_remote( array $target ): array|WP_Error {
        $host      = $target['host'];
        $owner     = $target['owner'];
        $repo      = $target['repo'];
        $is_github = ( 'github.com' === $host );

        if ( $is_github ) {
            $platform = 'github';
            $base     = "https://api.github.com/repos/{$owner}/{$repo}";
        } else {
            $platform = ( 'codeberg.org' === $host ) ? 'codeberg' : self::detect_software( $host );
            $base     = "https://{$host}/api/v1/repos/{$owner}/{$repo}";
        }

        $headers = self::headers( $is_github );
        $data    = self::get_json( $base, $headers );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        // GitHub uses `stargazers_count` and `homepage`,
        // Forgejo/Gitea use `stars_count` and `website`.
        $stars    = (int) ( $is_github ? ( $data['stargazers_count'] ?? 0 ) : ( $data['stars_count'] ?? 0 ) );
        $homepage = $is_github
            ? ( $data['homepage'] ?? '' )
            : ( ! empty( $data['website'] ) ? $data['website'] : ( $data['homepage'] ?? '' ) );
        $homepage = trim( (string) $homepage );

        $release = self::fetch_release( $is_github, $base, $headers );

        return [
            'full_name'      => (string) ( $data['full_name'] ?? rawurldecode( "{$owner}/{$repo}" ) ),
            'description'    => trim( (string) ( $data['description'] ?? '' ) ),
            'stars'          => $stars,
            'updated_at'     => (string) ( $data['updated_at'] ?? '' ),
            'homepage'       => wp_http_validate_url( $homepage ) ? $homepage : '',
            'repo_url'       => esc_url_raw( $target['url'] ),
            'platform'       => $platform,
            'platform_label' => self::platform_label( $host, $platform ),
            'avatar'         => esc_url_raw( (string) ( $data['owner']['avatar_url'] ?? '' ) ),
            'language'       => sanitize_text_field( (string) ( $data['language'] ?? '' ) ),
            'license'        => self::license( $is_github, $data ),
            'archived'       => ! empty( $data['archived'] ),
            'version'        => $release['version'],
            'download_url'   => $release['download_url'],
            'download_type'  => $release['download_type'],
        ];
    }

    /**
     * Request headers, with the optional token of the platform.
     *
     * @param  bool $is_github
     * @return array
     */
    private static function headers( bool $is_github ): array {
        $headers = [
            'Accept'     => $is_github ? 'application/vnd.github+json' : 'application/json',
            'User-Agent' => 'EG-Repo-Card/' . EG_REPO_CARD_VERSION . '; WordPress/' . get_bloginfo( 'version' ),
        ];

        if ( $is_github ) {
            $headers['X-GitHub-Api-Version'] = '2022-11-28';
            $token = (string) get_option( EG_Repo_Card_Settings::GITHUB_TOKEN, '' );
            if ( '' !== $token ) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
        } else {
            $token = (string) get_option( EG_Repo_Card_Settings::FORGEJO_TOKEN, '' );
            if ( '' !== $token ) {
                $headers['Authorization'] = 'token ' . $token;
            }
        }

        return $headers;
    }

    /**
     * GET a JSON document.
     *
     * @param  string $url
     * @param  array  $headers
     * @param  int    $timeout
     * @return array|WP_Error
     */
    private static function get_json( string $url, array $headers, int $timeout = 10 ): array|WP_Error {
        $response = wp_safe_remote_get( $url, [
            'timeout' => $timeout,
            'headers' => $headers,
        ] );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'eg_repo_card_api_error', $response->get_error_message() );
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return new WP_Error(
                'eg_repo_card_api_error',
                sprintf(
                    /* translators: %d: HTTP response status code */
                    __( 'API error: HTTP status %d.', 'eg-repo-card' ),
                    $code
                )
            );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) ) {
            return new WP_Error(
                'eg_repo_card_parse_error',
                __( 'Could not parse the API JSON response.', 'eg-repo-card' )
            );
        }

        return $data;
    }

    /**
     * Tell Forgejo from Gitea: only Forgejo answers /api/forgejo/v1/version.
     * The answer is cached per host for a week.
     *
     * @param  string $host
     * @return string  'forgejo' or 'gitea'.
     */
    private static function detect_software( string $host ): string {
        $key    = 'eg_repo_card_host_' . md5( $host );
        $cached = get_transient( $key );
        if ( is_string( $cached ) && '' !== $cached ) {
            return $cached;
        }

        $response = wp_safe_remote_get( "https://{$host}/api/forgejo/v1/version", [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            // Istanza irraggiungibile: nessuna conclusione, si riprova fra un'ora.
            set_transient( $key, 'forgejo', HOUR_IN_SECONDS );
            return 'forgejo';
        }

        $software = ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) ? 'forgejo' : 'gitea';
        set_transient( $key, $software, WEEK_IN_SECONDS );

        return $software;
    }

    /**
     * Resolve a human-readable platform label from the repository host.
     *
     * @param  string $host      Lower-cased repository host.
     * @param  string $platform  Platform slug (github|codeberg|forgejo|gitea).
     * @return string
     */
    private static function platform_label( string $host, string $platform ): string {
        $labels = [
            'github'   => 'GitHub',
            'codeberg' => 'Codeberg',
            'forgejo'  => 'Forgejo',
            'gitea'    => 'Gitea',
        ];

        $label = $labels[ $platform ] ?? 'Forgejo';

        $label = apply_filters_deprecated( 'egr_platform_label', [ $label, $host, $platform ], '2.0.0', 'eg_repo_card_platform_label' );

        /**
         * Filter the platform label shown on the repository card.
         *
         * @param string $label     The resolved label.
         * @param string $host      The repository host.
         * @param string $platform  The platform slug.
         */
        return (string) apply_filters( 'eg_repo_card_platform_label', $label, $host, $platform );
    }

    /**
     * License identifier. Forgejo and Gitea often return no license at all.
     *
     * @param  bool  $is_github
     * @param  array $data
     * @return string
     */
    private static function license( bool $is_github, array $data ): string {
        if ( $is_github ) {
            $license = (string) ( $data['license']['spdx_id'] ?? '' );
            if ( '' === $license || 'NOASSERTION' === $license ) {
                $license = (string) ( $data['license']['name'] ?? '' );
            }
            if ( 'Other' === $license ) {
                $license = '';
            }
        } else {
            $licenses = $data['licenses'] ?? [];
            $license  = is_array( $licenses ) && ! empty( $licenses ) ? (string) reset( $licenses ) : '';
        }

        return sanitize_text_field( $license );
    }

    /**
     * Latest release: version and download target.
     *
     * Download: il file .zip allegato se e' l'unico .zip della release,
     * altrimenti la pagina della release. Senza release la versione arriva
     * dall'ultimo tag git e il pulsante di download non compare.
     *
     * @param  bool   $is_github
     * @param  string $base     Repository API URL.
     * @param  array  $headers
     * @return array{version: string, download_url: string, download_type: string}
     */
    private static function fetch_release( bool $is_github, string $base, array $headers ): array {
        $result = [
            'version'       => '',
            'download_url'  => '',
            'download_type' => '',
        ];

        $url  = $is_github ? "{$base}/releases/latest" : "{$base}/releases?limit=1&draft=false&pre-release=false";
        $body = self::get_json( $url, $headers, 8 );

        if ( ! is_wp_error( $body ) ) {
            $release = $is_github ? $body : ( $body[0] ?? [] );
            $tag     = is_array( $release ) ? (string) ( $release['tag_name'] ?? '' ) : '';

            if ( '' !== $tag ) {
                $result['version'] = sanitize_text_field( $tag );

                $zips = array_values( array_filter(
                    (array) ( $release['assets'] ?? [] ),
                    static function ( $asset ) {
                        return is_array( $asset ) && str_ends_with( strtolower( (string) ( $asset['name'] ?? '' ) ), '.zip' );
                    }
                ) );

                if ( 1 === count( $zips ) && ! empty( $zips[0]['browser_download_url'] ) ) {
                    $result['download_url']  = esc_url_raw( (string) $zips[0]['browser_download_url'] );
                    $result['download_type'] = 'file';
                } elseif ( ! empty( $release['html_url'] ) ) {
                    $result['download_url']  = esc_url_raw( (string) $release['html_url'] );
                    $result['download_type'] = 'page';
                }

                return $result;
            }
        }

        // Fallback: tag git (repo che non usano le release)
        $tags_url = $is_github ? "{$base}/tags?per_page=1" : "{$base}/tags?limit=1";
        $tags     = self::get_json( $tags_url, $headers, 8 );

        if ( ! is_wp_error( $tags ) && ! empty( $tags[0]['name'] ) ) {
            $result['version'] = sanitize_text_field( (string) $tags[0]['name'] );
        }

        return $result;
    }

    /**
     * Format a star count to a compact human-readable string.
     * Examples: 0 → "0", 999 → "999", 1500 → "1.5k", 1200000 → "1.2M"
     *
     * @param  int    $count
     * @return string
     */
    public static function format_stars( int $count ): string {
        if ( $count >= 1_000_000 ) {
            return round( $count / 1_000_000, 1 ) . 'M';
        }
        if ( $count >= 1_000 ) {
            return round( $count / 1_000, 1 ) . 'k';
        }
        return (string) $count;
    }

    /**
     * Timestamp of an ISO 8601 date, 0 when missing or invalid.
     *
     * @param  string $iso_date
     * @return int
     */
    public static function timestamp( string $iso_date ): int {
        if ( '' === $iso_date ) {
            return 0;
        }
        $time = strtotime( $iso_date );
        return false === $time ? 0 : $time;
    }

    /**
     * Delete every cached result and start a new cache generation.
     *
     * La generation rende obsolete anche le entry in Redis/Memcached senza
     * doverle cancellare una per una. Il "last good" resta: serve proprio
     * quando l'API non risponde.
     */
    public static function flush_cache(): void {
        global $wpdb;

        update_option( EG_Repo_Card_Settings::CACHE_GEN, (int) get_option( EG_Repo_Card_Settings::CACHE_GEN, 1 ) + 1 );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk DELETE of transients by pattern, not cacheable
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                    OR option_name LIKE %s
                    OR option_name LIKE %s
                    OR option_name LIKE %s",
                $wpdb->esc_like( '_transient_eg_repo_card_data_' ) . '%',
                $wpdb->esc_like( '_transient_timeout_eg_repo_card_data_' ) . '%',
                $wpdb->esc_like( '_transient_eg_repo_card_host_' ) . '%',
                $wpdb->esc_like( '_transient_timeout_eg_repo_card_host_' ) . '%'
            )
        );
    }
}
