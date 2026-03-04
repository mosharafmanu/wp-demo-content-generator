=== WP Demo Content Generator ===
Contributors: mosharafmanu
Tags: demo content, dummy content, test data, content generator, sample content
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Quickly generate demo posts, pages, and custom post types with rich content and featured images — then safely delete only what was generated.

== Description ==

WP Demo Content Generator is a developer and agency tool for populating a WordPress site with realistic demo content in seconds. It works with any public post type — including custom post types registered by your theme or other plugins — and cleans up after itself without ever touching your real content.

**What it generates:**

* Posts, pages, or any registered custom post type
* Structured HTML content: headings (h2, h3), paragraphs, unordered lists, ordered lists, blockquotes, and inline links
* Unique gradient featured images created on the fly using PHP GD — no external service required
* Post excerpts with a configurable word limit
* Proper taxonomy term assignments for any detected taxonomy

**What makes it safe:**

Every item the plugin creates is stamped with a private post meta flag. When you delete, only those flagged items are removed — nothing else on your site is touched.

**Who it is for:**

* Developers building or testing a new theme
* Agencies preparing a client demo site
* Anyone who needs realistic-looking content fast and wants a clean way to remove it afterwards

== Installation ==

1. Upload the `wp-demo-content-generator` folder to the `/wp-content/plugins/` directory, or install directly from the WordPress plugin screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Tools → Demo Content** to start generating content.

== Frequently Asked Questions ==

= Will this plugin delete my real posts or pages? =

No. The plugin only deletes content that it created itself. Every generated item is marked internally, and the delete function filters strictly by that marker.

= What post types are supported? =

Any public post type registered on your site — including built-in types like Post and Page, and any custom post types added by your theme or plugins.

= Does it require any external service or API? =

No. Featured images are generated locally using PHP's GD extension. No external API calls are made.

= Does GD need to be enabled on my server? =

The GD extension is required only if you enable the featured image option. It is available on the vast majority of shared and managed hosts. If it is not available, all other features still work normally.

= Can I assign categories or tags to generated posts? =

Yes. The plugin automatically detects all taxonomies registered for the selected post type and lets you assign existing terms before generating.

= Is the generated content translatable? =

All plugin interface strings are translation-ready and use the `wp-demo-content-generator` text domain.

== Screenshots ==

1. The main admin page showing the generate form with all available options.
2. The delete section that appears once demo content has been created.

== Changelog ==

= 1.0.0 =
* Initial release.
* Generate posts, pages, and custom post types with full field control.
* PHP GD featured image generation with unique gradient designs per post.
* Rich HTML content blocks: headings, lists, blockquotes, and inline links.
* Taxonomy term assignment for any detected taxonomy.
* Safe tracked deletion — only plugin-generated content is removed.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.

