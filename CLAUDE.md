# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress child theme ("salient-child") for **fbhi.se**, built on the Salient parent theme (v13.1+).

## Deployment

```bash
# Deploy theme to production via rsync (from project root)
./upload.sh
```

This rsyncs `salient-child/` to the remote server at `fbhi.se`, using `--delete` to remove stale files on target.

## Coding Standards

- PHP 8.0+ — use modern PHP features where appropriate
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) for all PHP code

## Architecture

- **Parent theme**: Salient (premium WordPress theme)
- **Child theme**: `salient-child/` — all custom code lives here
- **ACF (Advanced Custom Fields)**: Used for hero images and font colors in the header
- **Custom post type**: `network-project` with taxonomy `network-project-category`, registered in `functions.php`

### Key Files

- `functions.php` — Registers custom post type, taxonomy, and enqueues stylesheets
- `header.php` — Custom header with ACF-driven hero image and font color
- `single-network-project.php` — Template for individual network project posts, uses partials from `includes/partials/single-network-project/`
- `css/build/style.css` — Main compiled stylesheet (large, ~7K lines)
- `css/fonts/` — Custom icomoon icon font

### Template Partials

`includes/partials/single-network-project/` contains:
- `content-area.php` — Project content display
- `sidebar.php` — Project metadata sidebar
- `bottom-project-navigation.php` — Previous/next project navigation
