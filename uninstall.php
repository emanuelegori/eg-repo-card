<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$eg_repo_card_options = [
    'eg_repo_card_settings',
    'eg_repo_card_github_token',
    'eg_repo_card_forgejo_token',
    'eg_repo_card_cache_gen',
];

foreach ( $eg_repo_card_options as $eg_repo_card_option ) {
    delete_option( $eg_repo_card_option );
}

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk DELETE of transients by pattern, not cacheable
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE %s
            OR option_name LIKE %s",
        $wpdb->esc_like( '_transient_eg_repo_card_' ) . '%',
        $wpdb->esc_like( '_transient_timeout_eg_repo_card_' ) . '%'
    )
);
