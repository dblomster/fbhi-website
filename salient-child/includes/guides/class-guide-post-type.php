<?php
/**
 * Guide post type registration.
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_Post_Type {

	public const POST_TYPE = 'guide';

	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 99 );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	public static function register_post_type(): void {
		$labels = array(
			'name'                  => _x( 'Guides', 'post type general name', 'salient-child' ),
			'singular_name'         => _x( 'Guide', 'post type singular name', 'salient-child' ),
			'menu_name'             => _x( 'Guides', 'admin menu', 'salient-child' ),
			'name_admin_bar'        => _x( 'Guide page', 'add new on admin bar', 'salient-child' ),
			'add_new'               => __( 'Add New', 'salient-child' ),
			'add_new_item'          => __( 'Add New Guide Page', 'salient-child' ),
			'new_item'              => __( 'New Guide Page', 'salient-child' ),
			'edit_item'             => __( 'Edit Guide Page', 'salient-child' ),
			'view_item'             => __( 'View Guide Page', 'salient-child' ),
			'all_items'             => __( 'All Guide Pages', 'salient-child' ),
			'search_items'          => __( 'Search Guide Pages', 'salient-child' ),
			'parent_item_colon'     => __( 'Parent Guide Page:', 'salient-child' ),
			'not_found'             => __( 'No guide pages found.', 'salient-child' ),
			'not_found_in_trash'    => __( 'No guide pages found in Trash.', 'salient-child' ),
			'item_published'        => __( 'Guide page published.', 'salient-child' ),
			'item_updated'          => __( 'Guide page updated.', 'salient-child' ),
			'attributes'            => __( 'Guide Page Attributes', 'salient-child' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Digital handbooks: a root guide page with chapters as child pages.', 'salient-child' ),
			'public'              => true,
			'hierarchical'        => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'rest_base'           => 'guides',
			'menu_position'       => 12,
			'menu_icon'           => 'dashicons-book-alt',
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'supports'            => array( 'title', 'editor', 'excerpt', 'revisions', 'page-attributes', 'custom-fields' ), // custom-fields: required for REST meta (Gutenberg panel).
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'can_export'          => true,
			'rewrite'             => array(
				'slug'         => FBHI_GUIDE_SLUG,
				'with_front'   => false,
				'hierarchical' => true,
			),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Flush rewrite rules once per module version so the new slug works
	 * right after deploy without a manual Settings → Permalinks visit.
	 */
	public static function maybe_flush_rewrite_rules(): void {
		if ( get_option( 'fbhi_guides_rewrite_version' ) === FBHI_GUIDES_VERSION . ':' . FBHI_GUIDE_SLUG ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'fbhi_guides_rewrite_version', FBHI_GUIDES_VERSION . ':' . FBHI_GUIDE_SLUG, false );
	}

	public static function title_placeholder( string $placeholder, \WP_Post $post ): string {
		if ( self::POST_TYPE === $post->post_type ) {
			return $post->post_parent
				? __( 'Chapter title', 'salient-child' )
				: __( 'Guide title', 'salient-child' );
		}
		return $placeholder;
	}

	public static function is_guide( $post = null ): bool {
		return self::POST_TYPE === get_post_type( $post );
	}
}
