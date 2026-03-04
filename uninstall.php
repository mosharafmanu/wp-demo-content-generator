<?php
/**
 * Uninstall WP Demo Content Generator
 *
 * This file runs automatically when the plugin is deleted via the WordPress admin.
 * It removes all options stored by the plugin. Generated posts are intentionally
 * left in place unless the user has already deleted them via the plugin UI —
 * respecting the principle of least surprise.
 *
 * @package WP_Demo_Content_Generator
 */

// Security: only run in a legitimate uninstall context.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// ─── Remove plugin options ────────────────────────────────────────────────────
delete_option( 'wpdcg_version' );
delete_option( 'wpdcg_generated_ids' );
delete_option( 'wpdcg_settings' );

