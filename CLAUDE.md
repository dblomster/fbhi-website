# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress child theme ("salient-child") for **fbhi.se**, built on the Salient parent theme (v15.0.9 as of 2026-08; officially WP 7.0 support starts at Salient 18.2, but 15.0.9 runs fine on WP 7.0.4 in practice — see docs/archive/updates-2026-08/ for the assessment and dev checklist).

## Deployment

```bash
# Deploy theme to production via rsync (from project root)
./upload.sh
```

This rsyncs `salient-child/` to the remote server at `fbhi.se`, using `--delete` to remove stale files on target. The script performs a dry-run comparison first (color-coded: green=add, yellow=overwrite, red=delete) and asks for confirmation before syncing.

**Caching**: Production runs behind Nginx caching. WP-level cache clearing is not enough — use the **Nginx Helper** plugin to purge the Nginx cache after deploying changes.

## Novamira MCP (PRODUCTION — handle with care)

The `novamira-fbhi-prod` MCP server (configured in `.mcp.json`) connects **directly to the live fbhi.se production site** via the Novamira / Novamira Pro plugins, authenticated as WordPress user ID 6 ("Daniel"). There is no staging in between — every write lands on prod immediately.

- **Read-only by default.** Freely use read abilities (list/get/inspect/check-setup). Any write ability (create/update/edit/delete/write/set/apply/activate) requires explicit user confirmation first — per-operation, not blanket.
- **Extra caution with the sharp tools**: `novamira/execute-php`, `novamira/run-wp-cli`, and `novamira/write-file` / `edit-file` / `delete-file` / `disable-file` are full code execution on the live server. Use them only when the user explicitly asks, never speculatively, and never for anything destructive without spelling out exactly what will run.
- **Never touch the connection itself**: do not update, deactivate, or delete the Novamira / Novamira Pro plugins, and do not modify user ID 6's credentials, application passwords, or Novamira OAuth connections — doing so severs the MCP connection.
- **Theme code still goes through git.** Do not use Novamira's file tools to edit `salient-child` on the server — that bypasses version control and gets clobbered by `upload.sh --delete`. Theme changes are made locally and deployed via `./upload.sh`.
- **Cache after writes**: content/settings changes on prod may be masked by Nginx caching — purge via Nginx Helper when verifying.

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
- **CSS**: `css/build/` contains Salient-generated compiled styles — **do not add custom CSS there** as it may be overwritten when theme options are re-exported. **All** custom CSS goes in `css/custom.css` (version-controlled, deployed by `upload.sh`). **Do not use Appearance → Customizer → Additional CSS** — it lives in the DB, outside git and the deploy pipeline. `custom.css` is split into two clearly-banner'd groups, and new rules go in the matching one:
  - **1. Theme overrides** — general tweaks to the Salient parent theme itself, not tied to any custom code (menus, accordions, typography…).
  - **2. Custom features** — CSS coupled to this child theme's bespoke code (CPT single templates, Events Calendar integration, shared partials…).

### Key Files

- `functions.php` — Registers blog-like CPT registry (`fbhi_blog_like_cpts()`), CPTs and taxonomies, enqueues stylesheets, WW-Fingers feature (network-project-only), and Events Calendar integration
- `header.php` — Custom header with ACF-driven hero image and font color
- `single-network-project.php` / `single-kommuner.php` — Thin per-CPT single templates that each `require` `includes/single-blog-like-cpt.php` (the shared Salient blog-style body)
- `includes/single-blog-like-cpt.php` — Shared single-template body for blog-like CPTs; looks up the archive URL for the bottom nav from `fbhi_blog_like_cpts()`
- `includes/partials/shared/bottom-post-navigation.php` — Reusable prev/next + "back to all" partial (accepts `archive_url` and optional `prev_label` / `next_label` / `back_title` via args)
- `css/build/style.css` — Salient-generated compiled stylesheet (~7K lines) — do not edit
- `css/custom.css` — **All** custom FBHI styles (enqueued after main-styles, cache-busted via filemtime), split into two groups: "1. Theme overrides" (general Salient tweaks) and "2. Custom features" (CSS for CPT templates, Events integration, shared partials)
- `css/fonts/` — Custom icomoon icon font

### Plugin Dependencies

- **Salient Portfolio** — Portfolio CSS conditionally loaded for every CPT listed in `fbhi_blog_like_cpts()` (provides the bottom-nav styling)
- **The Events Calendar** — Global sections injected before/after event list via `tribe_template` hooks
- **Advanced Custom Fields** — Header hero image and font color
- **WPML** — Multilingual support
- **Nginx Helper** — Cache purging on production
