# WP Demo Content Generator

A lightweight WordPress plugin to quickly fill a site with demo posts, pages, and custom post types — and cleanly remove them when you are done.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb3?logo=php)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green)

---

## Features

- Supports any public post type — posts, pages, or custom post types registered by your theme or other plugins
- Generates a unique gradient featured image for each post using PHP GD and saves it to the media library
- Builds structured HTML content with headings, paragraphs, lists, blockquotes, and inline links — not just plain lorem ipsum
- Optionally creates a post excerpt with a configurable word limit
- Lets you assign existing taxonomy terms to every generated post
- Choose any registered WordPress user as the author
- Set the post status to Published, Draft, or Pending Review
- Tracks everything it creates so deletion only ever touches plugin-generated content — your real content is safe
- Follows WordPress coding standards with nonce verification, capability checks, and escaped output

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.0 |
| PHP | 7.4 |
| PHP GD extension | Required for featured image generation |

---

## Installation

1. Clone or download this repository into your `wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/mosharafmanu/wp-demo-content-generator.git
   ```
2. Log in to your WordPress admin.
3. Go to **Plugins → Installed Plugins**.
4. Find **WP Demo Content Generator** and click **Activate**.

---

## Usage

Go to **Tools → Demo Content** in the WordPress admin menu.

### Generating content

| Field | Description |
|---|---|
| Post Type | Any public post type registered on the site |
| Count | How many items to create (1–500) |
| Post Status | Published, Draft, or Pending Review |
| Author | Any registered WordPress user |
| Paragraphs | Controls content depth per post (1–8) |
| Featured Image | Generates a unique gradient image and sets it as the featured image |
| Excerpt | Optionally adds an excerpt with a custom word limit |
| Assign Terms | Attaches existing taxonomy terms to each generated post |

Hit **Generate Demo Content** and the plugin logs every item it creates.

### Deleting content

Once generated content exists, a delete section appears at the bottom of the page. Tick the confirmation checkbox and click **Delete All Demo Content**. Only items created by this plugin are removed.

---

## Content structure

Each generated post is built as a proper article, not a wall of lorem ipsum. The structure varies per run and scales with the Paragraphs setting:

```
Intro paragraph
h2 + paragraph + list or blockquote
h2 + paragraph
h3 + paragraph + blockquote
Closing paragraph with an inline link
```

---

## Changelog

### 1.0.0
- Initial release
- Support for posts, pages, and custom post types
- PHP GD featured image generation
- Rich HTML content with headings, lists, blockquotes, and links
- Taxonomy term assignment
- Safe tracked deletion

---

## License

[GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author

**Mosharaf Hossain** — [mosharafmanu.com](https://mosharafmanu.com/)

