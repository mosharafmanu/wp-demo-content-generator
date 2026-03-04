<?php
/**
 * Admin page view — WP Demo Content Generator.
 * Rendered by WPDCG_Admin::render_page(). All output is escaped at point of output.
 *
 * @package WP_Demo_Content_Generator
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Notice ───────────────────────────────────────────────────────────────────
$notice_key = 'wpdcg_notice_' . get_current_user_id();
$notice     = get_transient( $notice_key );
if ( $notice ) { delete_transient( $notice_key ); }

// ── Page data ────────────────────────────────────────────────────────────────
$tracked      = WPDCG_Tracker::count();
$post_types   = get_post_types( array( 'public' => true ), 'objects' );
$preview_type = ( isset( $_GET['wpdcg_preview_type'] ) && post_type_exists( sanitize_key( $_GET['wpdcg_preview_type'] ) ) )
	? sanitize_key( $_GET['wpdcg_preview_type'] ) : 'post';
$taxonomies   = get_object_taxonomies( $preview_type, 'objects' );
$users        = get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) );
?>
<div class="wrap">
<h1><?php esc_html_e( 'WP Demo Content Generator', 'wp-demo-content-generator' ); ?></h1>

<?php if ( $notice ) : ?>
<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
	<p><?php echo esc_html( $notice['message'] ); ?></p>
</div>
<?php endif; ?>

<p><?php printf( esc_html( _n( '%d demo item currently tracked.', '%d demo items currently tracked.', $tracked, 'wp-demo-content-generator' ) ), (int) $tracked ); ?></p>

<?php /* ── Generate form ──────────────────────────────────────────────────── */ ?>
<div class="card" style="max-width:720px;padding:1.5em 2em;margin-bottom:1.5em;">
<h2 style="margin-top:0"><?php esc_html_e( 'Generate Demo Content', 'wp-demo-content-generator' ); ?></h2>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<?php wp_nonce_field( 'wpdcg_generate', 'wpdcg_generate_nonce' ); ?>
<input type="hidden" name="action" value="wpdcg_generate">

<table class="form-table" role="presentation">

<tr>
	<th scope="row"><label for="wpdcg_post_type"><?php esc_html_e( 'Post Type', 'wp-demo-content-generator' ); ?></label></th>
	<td>
		<select id="wpdcg_post_type" name="wpdcg_post_type">
			<?php foreach ( $post_types as $pt ) : ?>
			<option value="<?php echo esc_attr( $pt->name ); ?>"<?php selected( $pt->name, $preview_type ); ?>>
				<?php echo esc_html( $pt->labels->singular_name ); ?> (<?php echo esc_html( $pt->name ); ?>)
			</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Changing this reloads taxonomy options below.', 'wp-demo-content-generator' ); ?></p>
	</td>
</tr>

<tr>
	<th scope="row"><label for="wpdcg_count"><?php esc_html_e( 'Count', 'wp-demo-content-generator' ); ?></label></th>
	<td>
		<input type="number" id="wpdcg_count" name="wpdcg_count" value="5" min="1" max="500" class="small-text" required>
		<p class="description"><?php esc_html_e( '1–500 items per run.', 'wp-demo-content-generator' ); ?></p>
	</td>
</tr>

<tr>
	<th scope="row"><label for="wpdcg_status"><?php esc_html_e( 'Post Status', 'wp-demo-content-generator' ); ?></label></th>
	<td>
		<select id="wpdcg_status" name="wpdcg_status">
			<option value="publish"><?php esc_html_e( 'Published', 'wp-demo-content-generator' ); ?></option>
			<option value="draft"><?php esc_html_e( 'Draft', 'wp-demo-content-generator' ); ?></option>
			<option value="pending"><?php esc_html_e( 'Pending Review', 'wp-demo-content-generator' ); ?></option>
		</select>
	</td>
</tr>

<tr>
	<th scope="row"><label for="wpdcg_author"><?php esc_html_e( 'Author', 'wp-demo-content-generator' ); ?></label></th>
	<td>
		<select id="wpdcg_author" name="wpdcg_author">
			<?php foreach ( $users as $u ) : ?>
			<option value="<?php echo esc_attr( $u->ID ); ?>"<?php selected( $u->ID, get_current_user_id() ); ?>>
				<?php echo esc_html( $u->display_name ); ?>
			</option>
			<?php endforeach; ?>
		</select>
	</td>
</tr>

<tr>
	<th scope="row"><label for="wpdcg_paragraph_count"><?php esc_html_e( 'Paragraphs', 'wp-demo-content-generator' ); ?></label></th>
	<td>
		<input type="number" id="wpdcg_paragraph_count" name="wpdcg_paragraph_count" value="3" min="1" max="8" class="small-text">
		<p class="description"><?php esc_html_e( 'Content paragraphs per item (1–8).', 'wp-demo-content-generator' ); ?></p>
	</td>
</tr>

<tr>
	<th scope="row"><?php esc_html_e( 'Featured Image', 'wp-demo-content-generator' ); ?></th>
	<td>
		<label>
			<input type="checkbox" name="wpdcg_featured_image_generate" value="1">
			<?php esc_html_e( 'Auto-generate a placeholder featured image for each post', 'wp-demo-content-generator' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Creates a unique coloured gradient image via PHP GD, saves it to the media library, and sets it as the featured image. Requires the GD extension (enabled on most hosts).', 'wp-demo-content-generator' ); ?>
		</p>
	</td>
</tr>

<tr>
	<th scope="row"><?php esc_html_e( 'Excerpt', 'wp-demo-content-generator' ); ?></th>
	<td>
		<label>
			<input type="checkbox" id="wpdcg_exc_toggle" name="wpdcg_excerpt_enabled" value="1">
			<?php esc_html_e( 'Generate an excerpt for each post', 'wp-demo-content-generator' ); ?>
		</label>
		<div id="wpdcg-exc-wrap" style="display:none;margin-top:10px">
			<label for="wpdcg_excerpt_length"><?php esc_html_e( 'Excerpt length (words):', 'wp-demo-content-generator' ); ?></label>
			<input type="number" id="wpdcg_excerpt_length" name="wpdcg_excerpt_length" value="30" min="1" max="500" class="small-text" style="margin-left:5px">
		</div>
	</td>
</tr>

</table><!-- /.form-table -->

<?php /* ── Taxonomy / terms ──────────────────────────────────────────────── */ ?>
<?php if ( ! empty( $taxonomies ) ) : ?>
<div style="border-top:1px solid #ddd;margin:1.2em 0;padding-top:1.2em">
	<h3 style="margin-top:0"><?php esc_html_e( 'Assign Terms', 'wp-demo-content-generator' ); ?></h3>
	<p class="description">
		<?php
		printf(
			/* translators: %s: post type slug */
			esc_html__( 'Taxonomies detected for post type: %s. Change the Post Type above to refresh.', 'wp-demo-content-generator' ),
			'<strong>' . esc_html( $preview_type ) . '</strong>'
		);
		?>
	</p>
	<?php foreach ( $taxonomies as $tax_slug => $tax_obj ) :
		$terms = get_terms( array( 'taxonomy' => $tax_slug, 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) { continue; }
	?>
	<p>
		<label><strong><?php echo esc_html( $tax_obj->labels->name ); ?></strong></label><br>
		<select name="wpdcg_terms[<?php echo esc_attr( $tax_slug ); ?>][]" multiple size="5" style="min-width:220px">
			<?php foreach ( $terms as $term ) : ?>
			<option value="<?php echo esc_attr( $term->term_id ); ?>">
				<?php echo esc_html( $term->name ); ?> (<?php echo (int) $term->count; ?>)
			</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<?php submit_button( __( 'Generate Demo Content', 'wp-demo-content-generator' ), 'primary', 'wpdcg_submit_generate', false ); ?>
</form>
</div><!-- /.card generate -->

<?php /* ── Delete section (visible only when items are tracked) ──────────── */ ?>
<?php if ( $tracked > 0 ) : ?>
<div class="card" style="max-width:720px;padding:1.5em 2em;border-left:4px solid #d63638">
	<h2 style="margin-top:0"><?php esc_html_e( 'Delete Demo Content', 'wp-demo-content-generator' ); ?></h2>
	<p><?php esc_html_e( 'Permanently deletes all demo content created by this plugin. Real site content is never touched.', 'wp-demo-content-generator' ); ?></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'wpdcg_delete', 'wpdcg_delete_nonce' ); ?>
		<input type="hidden" name="action" value="wpdcg_delete">

		<p>
			<label>
				<input type="checkbox" name="wpdcg_confirm_delete" value="1">
				<strong><?php esc_html_e( 'I understand this action is irreversible and will permanently delete all demo content.', 'wp-demo-content-generator' ); ?></strong>
			</label>
		</p>

		<?php submit_button( __( 'Delete All Demo Content', 'wp-demo-content-generator' ), 'delete', 'wpdcg_submit_delete', false ); ?>
	</form>
</div>
<?php endif; ?>

</div><!-- /.wrap -->

<script>
jQuery( document ).ready( function ( $ ) {

	/* ── Post type change → server-side taxonomy reload ─────────────────── */
	$( '#wpdcg_post_type' ).on( 'change', function () {
		var url = new URL( window.location.href );
		url.searchParams.set( 'wpdcg_preview_type', $( this ).val() );
		window.location.href = url.toString();
	} );

	/* ── Excerpt toggle ──────────────────────────────────────────────────── */
	$( '#wpdcg_exc_toggle' ).on( 'change', function () {
		$( '#wpdcg-exc-wrap' ).toggle( this.checked );
	} );

} );
</script>

