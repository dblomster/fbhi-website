<?php
/**
 * FBHI Guides module bootstrap.
 *
 * Loads the guide (digital handbook) module: a hierarchical `guide` post type
 * whose root post is the guide index page and whose children are chapters.
 * See docs/guides/ in the repository for the plan and decision log.
 *
 * Core logic (post type, meta, hierarchy, TOC) is Salient-agnostic; only
 * class-guide-frontend.php and templates/ know about Salient markup.
 *
 * @package Salient-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** URL base for guides: /sv/{FBHI_GUIDE_SLUG}/{guide}/{chapter}/. Change here only. */
if ( ! defined( 'FBHI_GUIDE_SLUG' ) ) {
	define( 'FBHI_GUIDE_SLUG', 'guide' );
}

/** Bump when rewrite-affecting things change (slug, post type args) to flush permalinks once. */
define( 'FBHI_GUIDES_VERSION', '0.1.0' );
define( 'FBHI_GUIDES_DIR', __DIR__ );
define( 'FBHI_GUIDES_URL', get_stylesheet_directory_uri() . '/includes/guides' );
define( 'FBHI_GUIDES_ASSETS_DIR', get_stylesheet_directory() . '/assets/guides' );
define( 'FBHI_GUIDES_ASSETS_URL', get_stylesheet_directory_uri() . '/assets/guides' );

require_once FBHI_GUIDES_DIR . '/class-guide-post-type.php';
require_once FBHI_GUIDES_DIR . '/class-guide-meta.php';
require_once FBHI_GUIDES_DIR . '/class-guide-editor.php';
require_once FBHI_GUIDES_DIR . '/class-guide-hierarchy.php';
require_once FBHI_GUIDES_DIR . '/class-guide-toc.php';
require_once FBHI_GUIDES_DIR . '/class-guide-frontend.php';

add_action( 'after_setup_theme', function () {
	load_child_theme_textdomain( 'salient-child', get_stylesheet_directory() . '/languages' );
} );

FBHI\Guides\Guide_Post_Type::register();
FBHI\Guides\Guide_Meta::register();
FBHI\Guides\Guide_Editor::register();
FBHI\Guides\Guide_TOC::register();
FBHI\Guides\Guide_Frontend::register();
