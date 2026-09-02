<?php
/**
 * Frontend (Salient-facing) wiring for guides: assets, body class, menu
 * highlighting and small render helpers used by the templates.
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_Frontend {

	public static function register(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'highlight_menu' ), 20, 2 );
	}

	public static function is_guide_view(): bool {
		return is_singular( Guide_Post_Type::POST_TYPE );
	}

	public static function assets(): void {
		if ( ! self::is_guide_view() ) {
			return;
		}
		$ver = static fn( string $file ): string => file_exists( FBHI_GUIDES_ASSETS_DIR . '/' . $file )
			? (string) filemtime( FBHI_GUIDES_ASSETS_DIR . '/' . $file )
			: FBHI_GUIDES_VERSION;

		wp_enqueue_style( 'fbhi-guides', FBHI_GUIDES_ASSETS_URL . '/guides.css', array( 'fbhi-custom' ), $ver( 'guides.css' ) );
		if ( file_exists( FBHI_GUIDES_ASSETS_DIR . '/guides-print.css' ) ) {
			wp_enqueue_style( 'fbhi-guides-print', FBHI_GUIDES_ASSETS_URL . '/guides-print.css', array( 'fbhi-guides' ), $ver( 'guides-print.css' ), 'print' );
		}
		wp_enqueue_script( 'fbhi-guides', FBHI_GUIDES_ASSETS_URL . '/guides.js', array(), $ver( 'guides.js' ), true );
	}

	/** @param string[] $classes */
	public static function body_class( array $classes ): array {
		if ( self::is_guide_view() ) {
			$classes[] = 'fbhi-guide-page';
			$classes[] = Guide_Hierarchy::is_root( get_queried_object_id() ) ? 'fbhi-guide-page--root' : 'fbhi-guide-page--chapter';
		}
		return $classes;
	}

	/**
	 * Highlight the guide root's menu item (and its menu ancestors) on every
	 * page of that guide, and drop WP's "Posts page" fallback highlight.
	 * Same approach as fbhi_highlight_blog_like_cpt_menu_parent() in functions.php.
	 */
	public static function highlight_menu( array $items, $args ): array {
		if ( ! self::is_guide_view() ) {
			return $items;
		}
		$root = Guide_Hierarchy::root( get_queried_object_id() );
		if ( ! $root ) {
			return $items;
		}
		foreach ( $items as $item ) {
			$item->classes = array_values( array_diff( (array) $item->classes, array( 'current_page_parent' ) ) );
		}
		$target = null;
		foreach ( $items as $item ) {
			if ( Guide_Post_Type::POST_TYPE === $item->object && (int) $item->object_id === $root->ID ) {
				$target = $item;
				break;
			}
		}
		if ( ! $target ) {
			return $items;
		}
		$is_self = (int) get_queried_object_id() === $root->ID;
		$target->classes[] = $is_self ? 'current-menu-item' : 'current-menu-ancestor';
		$target->classes[] = $is_self ? 'current_page_item' : 'current_page_ancestor';

		$parent_db_id = (int) $target->menu_item_parent;
		$direct       = true;
		while ( $parent_db_id ) {
			$matched = false;
			foreach ( $items as $item ) {
				if ( (int) $item->db_id !== $parent_db_id ) {
					continue;
				}
				$item->classes[] = 'current-menu-ancestor';
				if ( $direct ) {
					$item->classes[] = 'current-menu-parent';
				}
				$parent_db_id = (int) $item->menu_item_parent;
				$direct       = false;
				$matched      = true;
				break;
			}
			if ( ! $matched ) {
				break;
			}
		}
		return $items;
	}

	/** Include a template part from includes/guides/templates/parts with $args in scope. */
	public static function part( string $name, array $args = array() ): void {
		$file = FBHI_GUIDES_DIR . '/templates/parts/' . $name . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		}
	}

	/**
	 * "Label – Title" for a guide page as safe HTML, with a divider between
	 * the parts when a label exists. The root renders as "Start".
	 */
	public static function page_name_html( \WP_Post $post, ?\WP_Post $root = null ): string {
		if ( $root && $post->ID === $root->ID ) {
			return esc_html__( 'Start', 'salient-child' );
		}
		$label = Guide_Meta::label( $post->ID );
		$title = esc_html( get_the_title( $post ) );
		if ( '' === $label ) {
			return $title;
		}
		return '<span class="fbhi-guide-page-name__label">' . esc_html( $label ) . '</span>'
			. '<span class="fbhi-guide-page-name__divider" aria-hidden="true">&ndash;</span>'
			. '<span class="fbhi-guide-page-name__title">' . $title . '</span>';
	}

	/** Manual excerpt only (never auto-generated from content). */
	public static function intro( \WP_Post $post ): string {
		return has_excerpt( $post ) ? wp_kses_post( $post->post_excerpt ) : '';
	}
}
