<?php
/**
 * Admin class for WP Demo Content Generator.
 *
 * Handles all WordPress admin interactions:
 * - Registering the admin menu page
 * - Processing nonce-verified form submissions (generate / delete)
 * - Passing transient notices back to the view
 *
 * @package WP_Demo_Content_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPDCG_Admin
 */
class WPDCG_Admin {

	/** @var string Capability required to use this plugin. */
	const CAPABILITY = 'manage_options';

	/** @var string Admin menu/page slug. */
	const MENU_SLUG = 'wp-demo-content-generator';

	/**
	 * Registers all admin-area hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu',                array( $this, 'register_menu' ) );
		add_action( 'admin_post_wpdcg_generate', array( $this, 'handle_generate' ) );
		add_action( 'admin_post_wpdcg_delete',   array( $this, 'handle_delete' ) );
	}

	/**
	 * Registers the plugin page under Tools in the admin menu.
	 */
	public function register_menu() {
		add_management_page(
			__( 'WP Demo Content Generator', 'wp-demo-content-generator' ),
			__( 'Demo Content', 'wp-demo-content-generator' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Renders the admin page by loading the view template.
	 */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-demo-content-generator' ) );
		}
		require_once WPDCG_PATH . 'admin/views/admin-page.php';
	}

	// ── Form Handlers ────────────────────────────────────────────────────────

	/**
	 * Handles the Generate form submission.
	 * Sanitizes every input, validates, then delegates to WPDCG_Generator.
	 */
	public function handle_generate() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'wp-demo-content-generator' ) );
		}
		check_admin_referer( 'wpdcg_generate', 'wpdcg_generate_nonce' );

		// ── Sanitize ─────────────────────────────────────────────────────────
		$post_type       = isset( $_POST['wpdcg_post_type'] )           ? sanitize_key( wp_unslash( $_POST['wpdcg_post_type'] ) ) : 'post';
		$count           = isset( $_POST['wpdcg_count'] )               ? absint( $_POST['wpdcg_count'] ) : 5;
		$status          = isset( $_POST['wpdcg_status'] )              ? sanitize_key( wp_unslash( $_POST['wpdcg_status'] ) ) : 'publish';
		$author_id       = isset( $_POST['wpdcg_author'] )              ? absint( $_POST['wpdcg_author'] ) : 0;
		$paragraph_count = isset( $_POST['wpdcg_paragraph_count'] )     ? absint( $_POST['wpdcg_paragraph_count'] ) : 3;
		$excerpt_enabled = ! empty( $_POST['wpdcg_excerpt_enabled'] );
		$excerpt_length  = isset( $_POST['wpdcg_excerpt_length'] )           ? absint( $_POST['wpdcg_excerpt_length'] ) : 30;
		$feat_enabled    = ! empty( $_POST['wpdcg_featured_image_generate'] );

		$taxonomy_terms = array();
		if ( isset( $_POST['wpdcg_terms'] ) && is_array( $_POST['wpdcg_terms'] ) ) {
			foreach ( $_POST['wpdcg_terms'] as $tax => $ids ) {
				$taxonomy_terms[ sanitize_key( $tax ) ] = array_map( 'absint', (array) $ids );
			}
		}

		// ── Validate ─────────────────────────────────────────────────────────
		if ( ! post_type_exists( $post_type ) ) {
			$this->set_notice( 'error', __( 'Invalid post type selected.', 'wp-demo-content-generator' ) );
			wp_safe_redirect( $this->page_url() ); exit;
		}
		if ( $count < 1 ) {
			$this->set_notice( 'error', __( 'Count must be at least 1.', 'wp-demo-content-generator' ) );
			wp_safe_redirect( $this->page_url() ); exit;
		}
		if ( $excerpt_enabled && $excerpt_length < 1 ) {
			$this->set_notice( 'error', __( 'Excerpt length must be at least 1 word.', 'wp-demo-content-generator' ) );
			wp_safe_redirect( $this->page_url() ); exit;
		}

		// ── Generate ─────────────────────────────────────────────────────────
		$result = ( new WPDCG_Generator() )->generate( array(
			'post_type'       => $post_type,
			'count'           => $count,
			'status'          => $status,
			'author_id'       => $author_id,
			'paragraph_count' => $paragraph_count,
			'excerpt_enabled' => $excerpt_enabled,
			'excerpt_length'  => $excerpt_length,
			'featured_image'  => $feat_enabled,
			'taxonomy_terms'  => $taxonomy_terms,
		) );

		if ( is_wp_error( $result ) ) {
			$this->set_notice( 'error', $result->get_error_message() );
		} else {
			$n = count( $result['created'] );
			$this->set_notice(
				'success',
				sprintf(
					/* translators: 1: item count, 2: batch ID string */
					_n( '%1$d demo item created. Batch ID: %2$s', '%1$d demo items created. Batch ID: %2$s', $n, 'wp-demo-content-generator' ),
					$n,
					$result['batch_id']
				)
			);
		}

		wp_safe_redirect( $this->page_url() );
		exit;
	}

	/**
	 * Handles the Delete form submission.
	 * Requires the confirmation checkbox to be checked.
	 */
	public function handle_delete() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'wp-demo-content-generator' ) );
		}
		check_admin_referer( 'wpdcg_delete', 'wpdcg_delete_nonce' );

		if ( empty( $_POST['wpdcg_confirm_delete'] ) ) {
			$this->set_notice( 'warning', __( 'Please check the confirmation checkbox before deleting demo content.', 'wp-demo-content-generator' ) );
			wp_safe_redirect( $this->page_url() );
			exit;
		}

		$result  = ( new WPDCG_Cleaner() )->delete_all();
		$deleted = $result['deleted'];

		$this->set_notice(
			'success',
			sprintf(
				/* translators: %d: number of items deleted */
				_n( '%d demo item permanently deleted.', '%d demo items permanently deleted.', $deleted, 'wp-demo-content-generator' ),
				$deleted
			)
		);

		wp_safe_redirect( $this->page_url() );
		exit;
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	/**
	 * Returns the canonical URL of the plugin admin page (under Tools).
	 *
	 * @return string
	 */
	private function page_url() {
		return admin_url( 'tools.php?page=' . self::MENU_SLUG );
	}

	/**
	 * Stores a transient admin notice scoped to the current user (60 s TTL).
	 *
	 * @param string $type    'success' | 'error' | 'warning'.
	 * @param string $message Human-readable notice text.
	 */
	private function set_notice( string $type, string $message ) {
		set_transient(
			'wpdcg_notice_' . get_current_user_id(),
			array(
				'type'    => $type,
				'message' => $message,
			),
			60
		);
	}
}

