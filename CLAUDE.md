# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress child theme ("salient-child") for **fbhi.se**, built on the Salient parent theme (v13.1+).

## Deployment

```bash
# Deploy theme to production via rsync (from project root)
./upload.sh
```

This rsyncs `salient-child/` to the remote server at `fbhi.se`, using `--delete` to remove stale files on target. The script performs a dry-run comparison first (color-coded: green=add, yellow=overwrite, red=delete) and asks for confirmation before syncing.

**Caching**: Production runs behind Nginx caching. WP-level cache clearing is not enough — use the **Nginx Helper** plugin to purge the Nginx cache after deploying changes.

## Coding Standards

- PHP 8.0+ — use modern PHP features where appropriate
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) for all PHP code

## Reference Files

`reference/` contains WordPress core and plugin source files for looking up hooks, filters, and internal APIs. **Read-only** — never modify these files. Use them to find the right hooks and understand WP internals when implementing features in the child theme.

## Architecture

- **Parent theme**: Salient (premium WordPress theme)
- **Child theme**: `salient-child/` — all custom code lives here
- **ACF (Advanced Custom Fields)**: Used for hero images and font colors in the header
- **Custom post types**: `network-project` (with taxonomy `network-project-category`) and `kommuner` (with taxonomy `kommuner-category`), registered in `functions.php`. Both use the Salient blog-style single layout via `includes/single-blog-like-cpt.php` and share the bottom prev/next nav. CPTs using this layout are listed in `fbhi_blog_like_cpts()` — see [docs/cpt-navigation.md](docs/cpt-navigation.md) to add another.
- **WPML**: Site is multilingual — custom code must be WPML-aware (use `wpml_object_id` filter for post lookups, `wpml_element_language_details` / `wpml_get_element_translations` for source post resolution)
- **CSS**: `css/build/` contains Salient-generated compiled styles — **do not add custom CSS there** as it may be overwritten when theme options are re-exported. All custom CSS goes in `css/custom.css`

### Key Files

- `functions.php` — Registers blog-like CPT registry (`fbhi_blog_like_cpts()`), CPTs and taxonomies, enqueues stylesheets, WW-Fingers feature (network-project-only), and Events Calendar integration
- `header.php` — Custom header with ACF-driven hero image and font color
- `single-network-project.php` / `single-kommuner.php` — Thin per-CPT single templates that each `require` `includes/single-blog-like-cpt.php` (the shared Salient blog-style body)
- `includes/single-blog-like-cpt.php` — Shared single-template body for blog-like CPTs; looks up the archive URL for the bottom nav from `fbhi_blog_like_cpts()`
- `includes/partials/shared/bottom-post-navigation.php` — Reusable prev/next + "back to all" partial (accepts `archive_url` and optional `prev_label` / `next_label` / `back_title` via args)
- `css/build/style.css` — Salient-generated compiled stylesheet (~7K lines) — do not edit
- `css/custom.css` — All custom FBHI styles (enqueued after main-styles, cache-busted via filemtime)
- `css/fonts/` — Custom icomoon icon font

### Plugin Dependencies

- **Salient Portfolio** — Portfolio CSS conditionally loaded for every CPT listed in `fbhi_blog_like_cpts()` (provides the bottom-nav styling)
- **The Events Calendar** — Global sections injected before/after event list via `tribe_template` hooks
- **Advanced Custom Fields** — Header hero image and font color
- **WPML** — Multilingual support
- **Nginx Helper** — Cache purging on production
