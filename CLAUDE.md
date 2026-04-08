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
- **Custom post type**: `network-project` with taxonomy `network-project-category`, registered in `functions.php`
- **WPML**: Site is multilingual — custom code must be WPML-aware (use `wpml_object_id` filter for post lookups, `wpml_element_language_details` / `wpml_get_element_translations` for source post resolution)
- **CSS**: `css/build/` contains Salient-generated compiled styles — **do not add custom CSS there** as it may be overwritten when theme options are re-exported. All custom CSS goes in `css/custom.css`

### Key Files

- `functions.php` — Registers custom post type, taxonomy, enqueues stylesheets, WW-Fingers feature, and Events Calendar integration
- `header.php` — Custom header with ACF-driven hero image and font color
- `single-network-project.php` — Template for individual network project posts, uses partials from `includes/partials/single-network-project/`
- `css/build/style.css` — Salient-generated compiled stylesheet (~7K lines) — do not edit
- `css/custom.css` — All custom FBHI styles (enqueued after main-styles, cache-busted via filemtime)
- `css/fonts/` — Custom icomoon icon font

### Template Partials

`includes/partials/single-network-project/` contains:
- `content-area.php` — Project content display
- `sidebar.php` — Project metadata sidebar
- `bottom-project-navigation.php` — Previous/next project navigation

### Plugin Dependencies

- **Salient Portfolio** — Portfolio CSS conditionally loaded for network-project posts
- **The Events Calendar** — Global sections injected before/after event list via `tribe_template` hooks
- **Advanced Custom Fields** — Header hero image and font color
- **WPML** — Multilingual support
- **Nginx Helper** — Cache purging on production
