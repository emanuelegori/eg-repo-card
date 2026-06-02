<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$options = [
    'egr_github_token',
    'egr_forgejo_token',
    'egr_cache_hours',
    'egr_card_bg_color',
    'egr_card_txt_color',
    'egr_btn_bg_color',
    'egr_btn_txt_color',
];

foreach ( $options as $option ) {
    delete_option( $option );
}

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
