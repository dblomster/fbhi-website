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
		add_action( 'template_redirect', array( __CLASS__, 'guard_unpublished' ) );
	}

	/**
	 * Unpublished guide trees stay hidden: a page whose ancestor (typically the
	 * guide root) is not published is a 404 for visitors, even if the page
	 * itself was published early. Editors can browse everything; those
	 * responses are never cached.
	 */
	public static function guard_unpublished(): void {
		if ( ! self::is_guide_view() ) {
			return;
		}
		$post_id = get_queried_object_id();
		$post    = get_post( $post_id );
		$blocked = Guide_Hierarchy::unpublished_ancestor( $post_id );
		if ( Guide_Hierarchy::can_preview_unpublished() ) {
			if ( $blocked || ( $post instanceof \WP_Post && Guide_Hierarchy::is_unpublished( $post ) ) ) {
				nocache_headers();
			}
			return;
		}
		if ( $blocked ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
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

	/** Small "Draft" style badge for unpublished pages, shown to editors only. Empty otherwise. */
	public static function status_badge_html( \WP_Post $post ): string {
		if ( ! Guide_Hierarchy::is_unpublished( $post ) || ! Guide_Hierarchy::can_preview_unpublished() ) {
			return '';
		}
		$label = Guide_Hierarchy::status_label( $post );
		return $label ? ' <span class="fbhi-guide-status fbhi-guide-status--' . esc_attr( $post->post_status ) . '">' . esc_html( $label ) . '</span>' : '';
	}

	/**
	 * Notice above the chapter header telling editors why visitors cannot see
	 * this page yet (its own status, or an unpublished guide root/ancestor).
	 */
	public static function preview_notice_html( \WP_Post $post, ?\WP_Post $root ): string {
		if ( ! Guide_Hierarchy::can_preview_unpublished() ) {
			return '';
		}
		$lines = array();
		if ( Guide_Hierarchy::is_unpublished( $post ) ) {
			switch ( $post->post_status ) {
				case 'pending':
					$lines[] = __( 'This page is pending review. Visitors cannot see it until it is published.', 'salient-child' );
					break;
				case 'future':
					$lines[] = __( 'This page is scheduled. Visitors cannot see it until it is published.', 'salient-child' );
					break;
				case 'private':
					$lines[] = __( 'This page is private. Only logged-in editors can see it.', 'salient-child' );
					break;
				default:
					$lines[] = __( 'This page is a draft. Visitors cannot see it until it is published.', 'salient-child' );
			}
		}
		$blocked = Guide_Hierarchy::unpublished_ancestor( $post->ID );
		if ( $blocked ) {
			$lines[] = ( $root && $blocked->ID === $root->ID )
				? __( 'The guide start page is not published yet, so the whole guide is hidden from visitors.', 'salient-child' )
				: __( 'A parent page of this page is not published, so visitors cannot reach it.', 'salient-child' );
		}
		if ( ! $lines ) {
			return '';
		}
		return '<div class="fbhi-guide-notice" role="status"><strong>' . esc_html__( 'Preview', 'salient-child' ) . '</strong> '
			. implode( ' ', array_map( 'esc_html', $lines ) ) . '</div>';
	}

	/** Manual excerpt only (never auto-generated from content). */
	public static function intro( \WP_Post $post ): string {
		return has_excerpt( $post ) ? wp_kses_post( $post->post_excerpt ) : '';
	}
}
