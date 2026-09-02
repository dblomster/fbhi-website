<?php
/**
 * Custom blocks for guides. Currently one: fbhi/guide-index (dynamic).
 * Also auto-appends the index to the guide root when the block is absent.
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_Blocks {

	public const INDEX_BLOCK = 'fbhi/guide-index';

	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'category' ), 10, 2 );
		add_filter( 'the_content', array( __CLASS__, 'auto_append_index' ), 20 );
	}

	public static function register_blocks(): void {
		$dir = FBHI_GUIDES_DIR . '/blocks/guide-index';
		$js  = $dir . '/editor.js';
		wp_register_script(
			'fbhi-guide-index-editor',
			FBHI_GUIDES_URL . '/blocks/guide-index/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			file_exists( $js ) ? (string) filemtime( $js ) : FBHI_GUIDES_VERSION,
			true
		);
		wp_set_script_translations( 'fbhi-guide-index-editor', 'salient-child', get_stylesheet_directory() . '/languages' );
		register_block_type( $dir );
	}

	/** @param array<int, array{slug:string,title:string}> $categories */
	public static function category( array $categories, $context ): array {
		array_unshift( $categories, array(
			'slug'  => 'fbhi-guide',
			'title' => __( 'FBHI Guide', 'salient-child' ),
			'icon'  => null,
		) );
		return $categories;
	}

	/**
	 * Render the chapter index for the guide that $post_id belongs to.
	 *
	 * @param array{heading?:string} $attributes
	 */
	public static function render_index( int $post_id, array $attributes = array(), string $wrapper_attributes = '' ): string {
		$root = Guide_Hierarchy::root( $post_id );
		if ( ! $root ) {
			return '';
		}
		$heading = trim( (string) ( $attributes['heading'] ?? '' ) );
		ob_start();
		Guide_Frontend::part( 'index-cards', array(
			'root'    => $root,
			'heading' => '' !== $heading ? $heading : __( 'Contents', 'salient-child' ),
			'wrapper' => $wrapper_attributes,
		) );
		return (string) ob_get_clean();
	}

	/**
	 * On the guide root, append the index after the content unless the
	 * editor placed the block somewhere themselves.
	 */
	public static function auto_append_index( string $content ): string {
		if ( ! Guide_Frontend::is_guide_view() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post_id = get_the_ID();
		if ( ! $post_id || ! Guide_Hierarchy::is_root( $post_id ) || has_block( self::INDEX_BLOCK, $post_id ) ) {
			return $content;
		}
		return $content . "\n" . self::render_index( $post_id );
	}
}
