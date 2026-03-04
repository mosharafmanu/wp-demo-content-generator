<?php
/**
 * Generator class for WP Demo Content Generator.
 *
 * Creates demo posts, pages, and CPT entries via wp_insert_post().
 * Every item is stamped with the `_wpdcg_generated` post meta flag and
 * its ID is recorded in WPDCG_Tracker so it can be safely removed later.
 *
 * @package WP_Demo_Content_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPDCG_Generator
 */
class WPDCG_Generator {

	/**
	 * Post meta key used to flag all generated content.
	 */
	const META_KEY = '_demo_content_generator_generated';

	/**
	 * Post meta key that stores the unique batch identifier.
	 */
	const BATCH_META_KEY = '_demo_content_generator_batch_id';

	/**
	 * Maximum number of items that can be generated in one request.
	 */
	const MAX_COUNT = 500;

	/**
	 * Sample titles for post type "post".
	 *
	 * @var string[]
	 */
	private static $post_titles = array(
		'Getting Started with WordPress Development',
		'Top 10 Tips for Building Fast Websites',
		'Understanding Custom Post Types',
		'A Complete Guide to WordPress Hooks',
		'Best Practices for WordPress Security',
		'How to Use the WordPress REST API',
		'Building Responsive Themes with WordPress',
		'Mastering the Block Editor',
		'How to Optimise Images for the Web',
		'The Ultimate Guide to WordPress SEO',
		'Getting Started with Advanced Custom Fields',
		'How to Create a Child Theme',
		'WordPress Performance: A Developer Checklist',
		'Deploying WordPress with CI/CD Pipelines',
		'Working with the WordPress Transients API',
	);

	/**
	 * Sample titles for post type "page".
	 *
	 * @var string[]
	 */
	private static $page_titles = array(
		'About Us',
		'Our Services',
		'Contact Us',
		'Frequently Asked Questions',
		'Our Team',
		'Portfolio',
		'Testimonials',
		'Get a Quote',
		'Privacy Policy',
		'Terms and Conditions',
	);

	/**
	 * Sample content paragraphs — mixed at random to build post body.
	 *
	 * @var string[]
	 */
	private static $content_blocks = array(
		'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
		'<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
		'<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt.</p>',
		'<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est qui dolorem ipsum quia dolor sit amet.</p>',
		'<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident similique.</p>',
		'<p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur et harum quidem rerum hic tenetur a sapiente.</p>',
		'<p>Nam libero tempore cum soluta nobis eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est et omnis dolor repellendus itaque earum rerum hic tenetur.</p>',
		'<p>Temporibus autem quibusdam et aut officiis debitis rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae itaque earum rerum hic tenetur a sapiente delectus ut aut.</p>',
	);

	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Generates demo content items.
	 *
	 * @param array $args {
	 *     @type string $post_type       Post type slug. Default 'post'.
	 *     @type int    $count           Number of items. Default 5. Max 500.
	 *     @type string $status          publish|draft|pending. Default 'publish'.
	 *     @type int    $author_id       Author user ID. 0 = current user.
	 *     @type int    $paragraph_count Paragraphs per post. Default 3.
	 *     @type bool   $excerpt_enabled Whether to generate an excerpt. Default false.
	 *     @type int    $excerpt_length  Word limit for excerpt. Default 30.
	 *     @type bool   $featured_image  Auto-generate a placeholder image. Default false.
	 *     @type array  $taxonomy_terms  [ taxonomy_slug => [ term_id, … ] ].
	 * }
	 * @return array|WP_Error Array with 'created', 'errors', 'batch_id'; or WP_Error.
	 */
	public function generate( array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'post_type'       => 'post',
				'count'           => 5,
				'status'          => 'publish',
				'author_id'       => 0,
				'paragraph_count' => 3,
				'excerpt_enabled' => false,
				'excerpt_length'  => 30,
				'featured_image'  => false,
				'taxonomy_terms'  => array(),
			)
		);

		$post_type       = sanitize_key( $args['post_type'] );
		$count           = max( 1, min( absint( $args['count'] ), self::MAX_COUNT ) );
		$status          = in_array( $args['status'], array( 'publish', 'draft', 'pending' ), true ) ? $args['status'] : 'publish';
		$author_id       = absint( $args['author_id'] ) ?: get_current_user_id();
		$paragraph_count = max( 1, absint( $args['paragraph_count'] ) );
		$excerpt_enabled = (bool) $args['excerpt_enabled'];
		$excerpt_length  = max( 1, absint( $args['excerpt_length'] ) );
		$featured_image  = (bool) $args['featured_image'];
		$taxonomy_terms  = is_array( $args['taxonomy_terms'] ) ? $args['taxonomy_terms'] : array();

		if ( ! post_type_exists( $post_type ) ) {
			return new WP_Error(
				'wpdcg_invalid_post_type',
				sprintf(
					/* translators: %s: post type slug */
					__( 'Post type "%s" does not exist.', 'wp-demo-content-generator' ),
					$post_type
				)
			);
		}

		$batch_id = 'batch_' . gmdate( 'Ymd_His' ) . '_' . substr( md5( uniqid( '', true ) ), 0, 6 );
		$created  = array();
		$errors   = array();

		for ( $i = 1; $i <= $count; $i++ ) {
			$title   = $this->get_title( $post_type, $i );
			$content = $this->get_content( $paragraph_count );
			$excerpt = $excerpt_enabled ? $this->get_excerpt( $content, $excerpt_length ) : '';

			$post_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
					'post_status'  => $status,
					'post_type'    => $post_type,
					'post_author'  => $author_id,
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				$errors[] = $post_id->get_error_message();
				continue;
			}

			update_post_meta( $post_id, self::META_KEY,       '1' );
			update_post_meta( $post_id, self::BATCH_META_KEY, $batch_id );

			if ( $featured_image ) {
				$this->generate_featured_image( $post_id, $title );
			}

			foreach ( $taxonomy_terms as $taxonomy => $term_ids ) {
				$taxonomy = sanitize_key( $taxonomy );
				$term_ids = array_map( 'absint', (array) $term_ids );
				if ( $taxonomy && ! empty( $term_ids ) ) {
					wp_set_object_terms( $post_id, $term_ids, $taxonomy );
				}
			}

			$created[] = $post_id;
		}

		if ( ! empty( $created ) ) {
			WPDCG_Tracker::add_ids( $created );
		}

		return array(
			'created'  => $created,
			'errors'   => $errors,
			'batch_id' => $batch_id,
		);
	}

	/**
	 * Picks a sample title for the given post type and iteration index.
	 * Cycles through the pool; appends a suffix after the first full cycle.
	 *
	 * @param string $post_type Post type slug.
	 * @param int    $index     1-based iteration counter.
	 * @return string
	 */
	private function get_title( string $post_type, int $index ) {
		$pool  = ( 'page' === $post_type ) ? self::$page_titles : self::$post_titles;
		$size  = count( $pool );
		$title = $pool[ ( $index - 1 ) % $size ];

		if ( $index > $size ) {
			$title .= ' ' . (int) ceil( $index / $size );
		}

		return $title;
	}

	/**
	 * Builds rich post content containing h2/h3, p, ul, ol, blockquote, and
	 * inline links. Structure scales with $paragraph_count:
	 *  - 1 intro <p>
	 *  - One section (heading + p + optional block) per remaining paragraph
	 *  - A closing <p> with an inline <a> link
	 *
	 * @param int $paragraph_count Controls overall length / depth.
	 * @return string HTML string ready for post_content.
	 */
	private function get_content( int $paragraph_count = 3 ): string {
		// ── Paragraph pool ─────────────────────────────────────────────────────
		$paras = self::$content_blocks;
		shuffle( $paras );
		$paras = array_values( $paras );

		// ── Heading pool ───────────────────────────────────────────────────────
		$h2 = array(
			'<h2>Getting Started</h2>',
			'<h2>Key Concepts to Understand</h2>',
			'<h2>Why This Matters</h2>',
			'<h2>Best Practices</h2>',
			'<h2>How It Works</h2>',
			'<h2>Core Principles</h2>',
			'<h2>Taking It Further</h2>',
		);
		$h3 = array(
			'<h3>A Closer Look</h3>',
			'<h3>Important Considerations</h3>',
			'<h3>Practical Tips</h3>',
			'<h3>Common Mistakes to Avoid</h3>',
			'<h3>Quick Summary</h3>',
			'<h3>Worth Knowing</h3>',
		);

		// ── Blockquote pool ────────────────────────────────────────────────────
		$quotes = array(
			'<blockquote><p>The best way to predict the future is to invent it. Good design is not just about aesthetics — it is about solving real problems for real people.</p></blockquote>',
			'<blockquote><p>Simplicity is the ultimate sophistication. Every complex problem has a solution that is clear, simple, and deceptively difficult to reach.</p></blockquote>',
			'<blockquote><p>First, solve the problem. Then, write the code. Clean code always looks like it was written by someone who cares deeply about their craft.</p></blockquote>',
			'<blockquote><p>Make it work, make it right, make it fast — in that order. The function of good software is to make the complex appear effortlessly simple.</p></blockquote>',
		);

		// ── Unordered list pool ────────────────────────────────────────────────
		$uls = array(
			"<ul>\n<li>Plan your project structure before writing a single line of code</li>\n<li>Write tests early and run them often throughout development</li>\n<li>Document your decisions as well as your implementations</li>\n<li>Review your own code as if a colleague wrote it</li>\n</ul>",
			"<ul>\n<li>Use meaningful names that reveal the intent behind each variable and function</li>\n<li>Keep functions small and focused on a single, well-defined responsibility</li>\n<li>Prefer composition over inheritance wherever it makes sense</li>\n<li>Refactor continuously — never leave code worse than you found it</li>\n</ul>",
			"<ul>\n<li>Performance optimisation should be driven by measurement, not assumption</li>\n<li>Security is not an afterthought — build it in from the very beginning</li>\n<li>Accessibility benefits every user, not only those with disabilities</li>\n<li>Consistency in style and patterns reduces cognitive load for everyone</li>\n</ul>",
			"<ul>\n<li>Version control every project, no matter how small</li>\n<li>Automate repetitive tasks to reduce human error</li>\n<li>Peer review catches problems that self-review will always miss</li>\n<li>Ship small, ship often, and iterate based on real feedback</li>\n</ul>",
		);

		// ── Ordered list pool ──────────────────────────────────────────────────
		$ols = array(
			"<ol>\n<li>Define the problem clearly before proposing any solution</li>\n<li>Research existing approaches and understand their trade-offs</li>\n<li>Design at a high level before diving into implementation details</li>\n<li>Build incrementally and validate at each milestone</li>\n<li>Gather feedback early and iterate until the goal is fully met</li>\n</ol>",
			"<ol>\n<li>Set up your development environment and initialise version control</li>\n<li>Create a minimal prototype to validate the core idea quickly</li>\n<li>Add features one at a time, testing thoroughly after each addition</li>\n<li>Conduct a full code review before any production release</li>\n<li>Monitor and measure after deployment to catch regressions early</li>\n</ol>",
			"<ol>\n<li>Identify your target audience and their primary needs</li>\n<li>Map out user journeys before designing any interface</li>\n<li>Create low-fidelity wireframes and gather stakeholder sign-off</li>\n<li>Build a high-fidelity prototype and run usability tests</li>\n<li>Refine based on findings and hand off to development</li>\n</ol>",
		);

		// ── Inline link closing paragraphs ─────────────────────────────────────
		$links = array(
			'<p>For further reading, visit the <a href="https://developer.wordpress.org" target="_blank" rel="noopener noreferrer">WordPress Developer Resources</a> — a comprehensive reference covering every core API.</p>',
			'<p>The <a href="https://wordpress.org/support/" target="_blank" rel="noopener noreferrer">WordPress Support Forums</a> are an excellent place to ask questions and share knowledge with the global community.</p>',
			'<p>Explore <a href="https://make.wordpress.org" target="_blank" rel="noopener noreferrer">Make WordPress</a> to learn how you can contribute to the project and collaborate with thousands of contributors worldwide.</p>',
			'<p>The <a href="https://wordpress.org/plugins/" target="_blank" rel="noopener noreferrer">Plugin Directory</a> hosts tens of thousands of extensions — a great source of inspiration and real-world code examples.</p>',
		);

		// ── Build document ─────────────────────────────────────────────────────
		$parts   = array();
		$used    = 0;
		$max_p   = min( $paragraph_count, count( $paras ) );

		// 1. Intro paragraph.
		$parts[] = $paras[ $used++ ];

		// 2. Sections: heading → paragraph → optional block element.
		$section = 0;
		while ( $used < $max_p ) {
			// Cycle h2 → h2 → h3 for a natural heading hierarchy.
			$parts[] = ( 2 === $section % 3 )
				? $h3[ array_rand( $h3 ) ]
				: $h2[ array_rand( $h2 ) ];

			$parts[] = $paras[ $used++ ];

			// On alternating sections inject a list or blockquote.
			if ( 0 === $section % 2 && $used < $max_p ) {
				switch ( mt_rand( 1, 3 ) ) {
					case 1:
						$parts[] = $uls[ array_rand( $uls ) ];
						break;
					case 2:
						$parts[] = $ols[ array_rand( $ols ) ];
						break;
					default:
						$parts[] = $quotes[ array_rand( $quotes ) ];
				}
			}

			$section++;
		}

		// 3. Closing paragraph with an inline link.
		$parts[] = $links[ array_rand( $links ) ];

		return implode( "\n\n", $parts );
	}

	/**
	 * Generates a word-limited plain-text excerpt from HTML post content.
	 *
	 * @param string $content    Raw HTML post content.
	 * @param int    $word_limit Maximum number of words.
	 * @return string
	 */
	private function get_excerpt( string $content, int $word_limit ) {
		$text  = wp_strip_all_tags( $content );
		$words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );

		if ( count( $words ) <= $word_limit ) {
			return $text;
		}

		return implode( ' ', array_slice( $words, 0, $word_limit ) ) . "\u{2026}";
	}

	/**
	 * Generates a professional placeholder image via PHP GD, registers it as a
	 * WordPress media attachment, stamps it with the plugin meta flag, and sets
	 * it as the featured image of the given post.
	 *
	 * Design: dark slate background, large decorative circles, vivid left accent
	 * bar, crisp title text (TTF if available, GD bitmap fallback otherwise), and
	 * a "DEMO CONTENT" label in a semi-opaque bottom band.
	 *
	 * @param int    $post_id    The post that will own the featured image.
	 * @param string $post_title Used as the overlay text on the image.
	 * @return int|false  Attachment ID on success, false if GD is unavailable.
	 */
	private function generate_featured_image( int $post_id, string $post_title ) {
		if ( ! function_exists( 'imagecreatetruecolor' ) ) {
			return false;
		}

		// ── Canvas ───────────────────────────────────────────────────────────
		$width  = 1200;
		$height = 630;
		$img    = imagecreatetruecolor( $width, $height );
		imagesavealpha( $img, true );

		// ── Vivid accent colour from a curated palette ────────────────────────
		$accents = array(
			array( 99, 102, 241 ),  // indigo
			array( 236, 72, 153 ),  // pink
			array( 16, 185, 129 ),  // emerald
			array( 245, 158, 11 ),  // amber
			array( 59, 130, 246 ),  // blue
			array( 239, 68, 68 ),   // red
		);
		$ac = $accents[ array_rand( $accents ) ];

		// ── Dark slate background ─────────────────────────────────────────────
		$bg = imagecolorallocate( $img, 15, 23, 42 );
		imagefilledrectangle( $img, 0, 0, $width, $height, $bg );

		// ── Large decorative circle — top-right ───────────────────────────────
		// Alpha 0 = fully opaque, 127 = fully transparent. Use ~55 for vivid glow.
		$c1 = imagecolorallocatealpha( $img, $ac[0], $ac[1], $ac[2], 55 );
		imagefilledellipse( $img, $width - 140, -90, 680, 680, $c1 );

		// ── Smaller decorative circle — bottom-left ───────────────────────────
		$c2 = imagecolorallocatealpha( $img, $ac[0], $ac[1], $ac[2], 70 );
		imagefilledellipse( $img, 90, $height + 90, 440, 440, $c2 );

		// ── Semi-opaque bottom band (content footer) ──────────────────────────
		$band = imagecolorallocatealpha( $img, 0, 0, 0, 55 );
		imagefilledrectangle( $img, 0, $height - 110, $width, $height, $band );

		// ── Vivid left accent bar ─────────────────────────────────────────────
		$bar = imagecolorallocate( $img, $ac[0], $ac[1], $ac[2] );
		imagefilledrectangle( $img, 0, 0, 8, $height, $bar );

		// ── Text colours ─────────────────────────────────────────────────────
		$white = imagecolorallocate( $img, 255, 255, 255 );
		$muted = imagecolorallocate( $img, 148, 163, 184 );
		$hilit = imagecolorallocate( $img, $ac[0], $ac[1], $ac[2] );

		// ── Render text: TTF (crisp) or GD bitmap (fallback) ─────────────────
		$font_path = $this->find_ttf_font();

		if ( $font_path && function_exists( 'imagettftext' ) ) {
			// Wrap at 70 % of canvas width. imagettfbbox() can underestimate
			// Inter Bold's advance width, so a 15 % margin on each side prevents
			// lines from escaping the canvas even after theme cropping.
			$size   = 52;
			$safe_w = (int) ( $width * 0.70 ); // 840 px on a 1200 px canvas.
			$lines  = $this->ttf_wrap_text( $font_path, $size, $post_title, $safe_w );
			$lh     = (int) ( $size * 1.38 );
			$total  = count( $lines ) * $lh;
			$y0     = (int) ( ( ( $height - 110 ) / 2 ) - ( $total / 2 ) + $size );

			// Centre each line using its real measured width + 4 % safety buffer.
			foreach ( $lines as $i => $line ) {
				$box = imagettfbbox( $size, 0, $font_path, $line );
				$lw  = (int) ( abs( $box[4] - $box[0] ) * 1.04 ); // 4 % overrun guard.
				$x   = max( (int) ( $width * 0.05 ), (int) ( ( $width - $lw ) / 2 ) );
				imagettftext( $img, $size, 0, $x, $y0 + $i * $lh, $white, $font_path, $line );
			}

			// Bottom band: "DEMO CONTENT  wp-demo-content-generator" — centred together.
			$demo  = 'DEMO CONTENT';
			$slug  = 'wp-demo-content-generator';
			$gap   = 24;
			$bd    = imagettfbbox( 15, 0, $font_path, $demo );
			$bs    = imagettfbbox( 13, 0, $font_path, $slug );
			$dw    = abs( $bd[4] - $bd[0] );
			$sw    = abs( $bs[4] - $bs[0] );
			$bx    = (int) ( ( $width - $dw - $gap - $sw ) / 2 );
			imagettftext( $img, 15, 0, $bx,              $height - 38, $hilit, $font_path, $demo );
			imagettftext( $img, 13, 0, $bx + $dw + $gap, $height - 38, $muted, $font_path, $slug );
		} else {
			// GD bitmap fallback — centred.
			$font  = 5;
			$fw    = imagefontwidth( $font );
			$fh    = imagefontheight( $font );
			$label = mb_strimwidth( $post_title, 0, (int) floor( ( $width * 0.8 ) / $fw ), '...' );
			$lw    = strlen( $label ) * $fw;
			imagestring( $img, $font, (int) ( ( $width - $lw ) / 2 ), (int) ( ( ( $height - 110 ) / 2 ) - ( $fh / 2 ) ), $label, $white );
			$sub = 'DEMO CONTENT';
			imagestring( $img, 3, (int) ( ( $width - strlen( $sub ) * imagefontwidth( 3 ) ) / 2 ), $height - 50, $sub, $hilit );
		}

		// ── Save to uploads ───────────────────────────────────────────────────
		$upload   = wp_upload_dir();
		$filename = 'wpdcg-' . $post_id . '-' . substr( md5( (string) $post_id ), 0, 6 ) . '.jpg';
		$filepath = trailingslashit( $upload['path'] ) . $filename;
		imagejpeg( $img, $filepath, 90 );
		imagedestroy( $img );

		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		// ── Register as media attachment ─────────────────────────────────────
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => trailingslashit( $upload['url'] ) . $filename,
				'post_mime_type' => 'image/jpeg',
				'post_title'     => $post_title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$filepath,
			$post_id
		);

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $filepath ) );
		update_post_meta( $attachment_id, self::META_KEY, '1' );
		set_post_thumbnail( $post_id, $attachment_id );

		return $attachment_id;
	}

	/**
	 * Locates a TrueType font file to use for image text rendering.
	 *
	 * Priority:
	 *  1. Inter Bold bundled with this plugin (assets/fonts/Inter-Bold.ttf) —
	 *     guaranteed to exist if the plugin was installed correctly.
	 *  2. System fonts: macOS supplemental directory, then Linux, then Windows.
	 *
	 * @return string Absolute path to a .ttf file, or '' if nothing found.
	 */
	private function find_ttf_font(): string {
		// 1. Bundled font — always preferred (Inter Bold, SIL OFL licence).
		$bundled = dirname( __FILE__ ) . '/../assets/fonts/Inter-Bold.ttf';
		if ( file_exists( $bundled ) ) {
			return realpath( $bundled );
		}

		// 2. System fonts — ordered by likelihood of availability.
		$candidates = array(
			// macOS — supplemental fonts (confirmed present on macOS 12+).
			'/System/Library/Fonts/Supplemental/Arial Bold.ttf',
			'/System/Library/Fonts/Supplemental/Verdana Bold.ttf',
			'/System/Library/Fonts/Supplemental/Trebuchet MS Bold.ttf',
			'/System/Library/Fonts/Supplemental/DIN Alternate Bold.ttf',
			'/System/Library/Fonts/Supplemental/Georgia Bold.ttf',
			'/System/Library/Fonts/Supplemental/Tahoma Bold.ttf',
			'/System/Library/Fonts/Supplemental/Arial.ttf',
			// macOS — /Library/Fonts (e.g. installed by MS Office).
			'/Library/Fonts/Arial Bold.ttf',
			'/Library/Fonts/Arial.ttf',
			// Linux — DejaVu (present on almost every distro).
			'/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
			'/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
			'/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
			'/usr/share/fonts/liberation/LiberationSans-Bold.ttf',
			'/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
			// Windows.
			'C:\\Windows\\Fonts\\arialbd.ttf',
			'C:\\Windows\\Fonts\\arial.ttf',
		);

		foreach ( $candidates as $path ) {
			if ( file_exists( $path ) ) {
				return $path;
			}
		}

		return '';
	}

	/**
	 * Wraps a string so that each line fits within $max_width pixels when
	 * rendered at the given font size. Returns an array of line strings, capped
	 * at three lines (the last line gets an ellipsis if truncated).
	 *
	 * @param string $font      Path to the TTF font file.
	 * @param int    $size      Font size in points.
	 * @param string $text      Input text to wrap.
	 * @param int    $max_width Maximum pixel width per line.
	 * @return string[]
	 */
	private function ttf_wrap_text( string $font, int $size, string $text, int $max_width ): array {
		$words = explode( ' ', $text );
		$lines = array();
		$line  = '';

		foreach ( $words as $word ) {
			$test = $line === '' ? $word : $line . ' ' . $word;
			$box  = imagettfbbox( $size, 0, $font, $test );
			if ( abs( $box[4] - $box[0] ) > $max_width && $line !== '' ) {
				$lines[] = $line;
				$line    = $word;
			} else {
				$line = $test;
			}
		}

		if ( $line !== '' ) {
			$lines[] = $line;
		}

		// Hard cap at 3 lines to prevent overflow into the bottom band.
		if ( count( $lines ) > 3 ) {
			$lines    = array_slice( $lines, 0, 3 );
			$lines[2] .= '…';
		}

		return $lines;
	}
}
