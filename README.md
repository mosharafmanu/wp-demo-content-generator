# WP Demo Content Generator

> A lightweight WordPress plugin to generate rich demo content — posts, pages, and custom post types — and safely delete only what was generated.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb3?logo=php)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green)

---

## ✨ Features

- 📝 **Any post type** — generate demo content for posts, pages, or any registered custom post type
- 🖼️ **Auto-generated featured images** — unique coloured gradient images created via PHP GD, saved directly to the media library
- 📄 **Rich HTML content** — structured articles with `h2`, `h3`, `p`, `ul`, `ol`, `blockquote`, and inline `a` links
- ✍️ **Excerpt support** — optionally generate and set a post excerpt with a configurable word limit
- 🏷️ **Taxonomy & term assignment** — assign existing terms to generated posts for any detected taxonomy
- 👤 **Author selection** — choose any registered user as the post author
- 📊 **Status control** — generate as Published, Draft, or Pending Review
- 🗑️ **Safe deletion** — removes only what the plugin created; real site content is never touched
- 🔒 **Security-first** — nonce verification, capability checks, and fully escaped output throughout

---

## 🔧 Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.0 |
| PHP | 7.4 |
| PHP GD extension | Required for featured image generation |

---

## 📦 Installation

1. Download or clone this repository into your `wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/mosharafmanu/wp-demo-content-generator.git
   ```
2. Log in to your WordPress admin panel.
3. Go to **Plugins → Installed Plugins**.
4. Find **WP Demo Content Generator** and click **Activate**.

---

## 🚀 Usage

Navigate to **Tools → Demo Content** in the WordPress admin menu.

### Generate Content

| Field | Description |
|---|---|
| **Post Type** | Select any public post type (post, page, or custom) |
| **Count** | Number of items to generate (1–500) |
| **Post Status** | Published / Draft / Pending Review |
| **Author** | Assign a registered WordPress user |
| **Paragraphs** | Content depth per post (1–8) |
| **Featured Image** | Auto-generate a unique gradient image via PHP GD |
| **Excerpt** | Optionally generate an excerpt with a custom word limit |
| **Assign Terms** | Assign existing taxonomy terms to each generated post |

Click **Generate Demo Content** — the plugin tracks everything it creates.

### Delete Content

When generated content exists, a **Delete Demo Content** section appears. Confirm the irreversible action and click **Delete All Demo Content**. Only plugin-generated content is removed.

---

## 📄 Generated Content Structure

Each post body is built dynamically and varies per generation run:

```
Intro paragraph
└── h2 + paragraph + ul / ol / blockquote  (alternating)
└── h2 + paragraph
└── h3 + paragraph + blockquote
└── Closing paragraph with inline <a> link
```

Content scales in depth with the **Paragraphs** setting.

---

## 📋 Changelog

### 1.0.0
- Initial release
- Post / page / CPT generation with full options
- PHP GD featured image generation
- Rich HTML content blocks (headings, lists, blockquotes, links)
- Taxonomy term assignment
- Safe tracked deletion

---

## 📜 License

Licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

## 👤 Author

**Mosharaf Hossain**
🌐 [mosharafmanu.com](https://mosharafmanu.com/)
🐙 [github.com/mosharafmanu](https://github.com/mosharafmanu)

