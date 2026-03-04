<?php
/**
 * Core class for WP Demo Content Generator.
 *
 * Bootstraps the plugin: instantiates dependencies and registers all hooks.
 * Uses the singleton pattern to ensure only one instance is ever created.
 *
 * @package WP_Demo_Content_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPDCG_Core
 */
class WPDCG_Core {

	/**
	 * Singleton instance.
	 *
	 * @var WPDCG_Core|null
	 */
	private static $instance = null;

	/**
	 * Returns the single instance of this class.
	 *
	 * @return WPDCG_Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Loads admin-only dependencies.
	 * Front-end has no output; this plugin is admin-only.
	 */
	private function load_dependencies() {
		if ( is_admin() ) {
			require_once WPDCG_PATH . 'admin/class-wpdcg-admin.php';
		}
	}

	/**
	 * Registers WordPress hooks.
	 */
	private function init_hooks() {
		// Load plugin text domain for translations.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Boot the admin layer.
		if ( is_admin() ) {
			new WPDCG_Admin();
		}
	}

	/**
	 * Loads the plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-demo-content-generator',
			false,
			dirname( WPDCG_BASENAME ) . '/languages'
		);
	}

	/**
	 * Prevent cloning of the singleton instance.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialising of the singleton instance.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}
}

