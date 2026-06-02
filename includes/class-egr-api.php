<?php
/**
 * EGR_API
 * Handles all remote API calls (GitHub REST API v3, Forgejo/Gitea API v1).
 * Results are cached via WordPress transients.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EGR_API {

    /**
     * Fetch repository data from GitHub or any Forgejo/Gitea instance.
     *
     * @param  string $url  Full repository URL, e.g. https://github.com/owner/repo
     * @return array|WP_Error  Normalised repo data array on success, WP_Error on failure.
     */
    public static function fetch( string $url ): array|WP_Error {
        $url = trim( $url );

        // Parse the URL
        $parsed = wp_parse_url( $url );

        if ( ! $parsed || empty( $parsed['host'] ) || empty( $parsed['path'] ) ) {
            return new WP_Error(
                'egr_invalid_url',
                __( 'Invalid repository URL.', 'eg-ranking-repo' )
            );
        }

        $host  = strtolower( $parsed['host'] );
        $parts = array_values( array_filter( explode( '/', trim( $parsed['path'], '/' ) ) ) );

        if ( count( $parts ) < 2 ) {
            return new WP_Error(
                'egr_invalid_url',
                __( 'Invalid repository URL: missing owner/repo in path.', 'eg-ranking-repo' )
            );
        }

        $owner     = $parts[0];
        $repo      = $parts[1];
        $is_github = ( $host === 'github.com' );

        // Transient cache key: unique per generation + host + owner + repo.
        // La generation viene incrementata ad ogni flush manuale, rendendo
        // obsolete le entry precedenti senza dover svuotare Redis/Memcached.
        $gen       = (int) get_option( 'egr_cache_gen', 1 );
        $cache_key = 'egr_' . $gen . '_' . md5( "{$host}/{$owner}/{$repo}" );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            if ( isset( $cached['egr_error'] ) ) {
                return new WP_Error( $cached['egr_error_code'] ?? 'egr_api_error', $cached['egr_error'] );
            }
            // Cache da versione precedente senza campo 'version': lascia passare per rifare il fetch
            if ( array_key_exists( 'version', $cached ) ) {
                return $cached;
            }
        }

        // Build API endpoint
        if ( $is_github ) {
            $api_url = "https://api.github.com/repos/{$owner}/{$repo}";
        } else {
            $api_url = "https://{$host}/api/v1/repos/{$owner}/{$repo}";
        }

        // Build request headers
        $headers = [
            'Accept'     => $is_github
                ? 'application/vnd.github+json'
                : 'application/json',
            'User-Agent' => 'EG-Ranking-Repo/' . EGR_VERSION . '; WordPress/' . get_bloginfo( 'version' ),
        ];

        if ( $is_github ) {
            $headers['X-GitHub-Api-Version'] = '2022-11-28';
            $token = get_option( 'egr_github_token', '' );
            if ( ! empty( $token ) ) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
        } else {
            $token = get_option( 'egr_forgejo_token', '' );
            if ( ! empty( $token ) ) {
                $headers['Authorization'] = 'token ' . $token;
            }
        }

        $response = wp_safe_remote_get( $api_url, [
            'timeout' => 10,
            'headers' => $headers,
        ] );

        if ( is_wp_error( $response ) ) {
            $msg = $response->get_error_message();
            set_transient( $cache_key, [ 'egr_error_code' => 'egr_api_error', 'egr_error' => $msg ], 5 * MINUTE_IN_SECONDS );
            return new WP_Error( 'egr_api_error', $msg );
        }

        $http_code = wp_remote_retrieve_response_code( $response );

        if ( 200 !== (int) $http_code ) {
            $msg = sprintf(
                /* translators: %d: HTTP response status code */
                __( 'API error: HTTP status %d.', 'eg-ranking-repo' ),
                (int) $http_code
            );
            set_transient( $cache_key, [ 'egr_error_code' => 'egr_api_error', 'egr_error' => $msg ], 5 * MINUTE_IN_SECONDS );
            return new WP_Error( 'egr_api_error', $msg );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! is_array( $data ) ) {
            $msg = __( 'Could not parse the API JSON response.', 'eg-ranking-repo' );
            set_transient( $cache_key, [ 'egr_error_code' => 'egr_parse_error', 'egr_error' => $msg ], 5 * MINUTE_IN_SECONDS );
            return new WP_Error( 'egr_parse_error', $msg );
        }

        // Normalise fields.
        // GitHub uses `stargazers_count`, Forgejo uses `stars_count`.
        // GitHub uses `homepage`, Forgejo uses `website` (but also supports `homepage`).
        $stars    = $is_github
            ? (int) ( $data['stargazers_count'] ?? 0 )
            : (int) ( $data['stars_count'] ?? 0 );

        $homepage = '';
        if ( $is_github ) {
            $homepage = $data['homepage'] ?? '';
        } else {
            $homepage = ! empty( $data['website'] ) ? $data['website'] : ( $data['homepage'] ?? '' );
        }
        $homepage = trim( (string) $homepage );

        $result = [
            'name'        => $data['name']        ?? $repo,
            'full_name'   => $data['full_name']   ?? "{$owner}/{$repo}",
            'description' => trim( (string) ( $data['description'] ?? '' ) ),
            'stars'       => $stars,
            'updated_at'  => $data['updated_at']  ?? '',
            'homepage'    => filter_var( $homepage, FILTER_VALIDATE_URL ) ? $homepage : '',
            'repo_url'    => esc_url_raw( rtrim( $url, '/' ) ),
            'platform'    => $is_github ? 'github' : 'forgejo',
            'version'     => self::fetch_latest_version( $is_github, $host, $owner, $repo, $headers ),
        ];

        // Cache result
        $cache_hours = (int) get_option( 'egr_cache_hours', 6 );
        set_transient( $cache_key, $result, $cache_hours * HOUR_IN_SECONDS );

        return $result;
    }

    /**
     * Fetch the tag_name of the latest release from GitHub or Forgejo.
     * Returns '' if no releases exist or the request fails.
     */
    private static function fetch_latest_version( bool $is_github, string $host, string $owner, string $repo, array $headers ): string {
        // Prova prima le releases (formato semanticamente corretto)
        $url = $is_github
            ? "https://api.github.com/repos/{$owner}/{$repo}/releases/latest"
            : "https://{$host}/api/v1/repos/{$owner}/{$repo}/releases?limit=1";

        $response = wp_safe_remote_get( $url, [ 'timeout' => 8, 'headers' => $headers ] );
        $code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );

        if ( 200 === $code ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( is_array( $body ) ) {
                $tag = $is_github ? ( $body['tag_name'] ?? '' ) : ( $body[0]['tag_name'] ?? '' );
                if ( '' !== $tag ) {
                    return sanitize_text_field( (string) $tag );
                }
            }
        }

        // Fallback: tag git (repo che non usano le releases ufficiali)
        $tags_url = $is_github
            ? "https://api.github.com/repos/{$owner}/{$repo}/tags"
            : "https://{$host}/api/v1/repos/{$owner}/{$repo}/tags?limit=1";

        $tags_response = wp_safe_remote_get( $tags_url, [ 'timeout' => 8, 'headers' => $headers ] );

        if ( is_wp_error( $tags_response ) || 200 !== (int) wp_remote_retrieve_response_code( $tags_response ) ) {
            return '';
        }

        $tags = json_decode( wp_remote_retrieve_body( $tags_response ), true );
        if ( ! is_array( $tags ) || empty( $tags ) ) {
            return '';
        }

        return sanitize_text_field( (string) ( $tags[0]['name'] ?? '' ) );
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
     * Format an ISO 8601 date string to dd-mm-yyyy.
     *
     * @param  string $iso_date
     * @return string
     */
    public static function format_date( string $iso_date ): string {
        if ( empty( $iso_date ) ) {
            return __( 'N/A', 'eg-ranking-repo' );
        }
        try {
            $dt = new DateTime( $iso_date, new DateTimeZone( 'UTC' ) );
            $dt->setTimezone( wp_timezone() );
            return $dt->format( 'd-m-Y' );
        } catch ( Exception $e ) {
            return __( 'N/A', 'eg-ranking-repo' );
        }
    }
}
