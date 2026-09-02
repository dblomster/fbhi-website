<?php
/**
 * Editor experience for guides: curated block list, editor assets for the
 * "Guide page" settings panel (label + accent), and admin list columns.
 *
 * Block styles and patterns (phase 4) are also registered here.
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_Editor {

	public static function register(): void {
		add_filter( 'allowed_block_types_all', array( __CLASS__, 'allowed_blocks' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor_assets' ) );
		add_filter( 'manage_' . Guide_Post_Type::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . Guide_Post_Type::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
	}

	/**
	 * Blocks editors may use on guide pages. Everything else (Salient/plugin
	 * blocks, embeds of random services, cover, media-text …) is hidden.
	 *
	 * @return string[]
	 */
	public static function allowed_block_list(): array {
		$blocks = array(
			'core/paragraph',
			'core/heading',
			'core/list',
			'core/list-item',
			'core/image',
			'core/gallery',
			'core/video',
			'core/embed',
			'core/buttons',
			'core/button',
			'core/quote',
			'core/group',
			'core/columns',
			'core/column',
			'core/table',
			'core/file',
			'core/separator',
			'core/spacer',
			'fbhi/guide-index',
		);
		return apply_filters( 'fbhi_guide_allowed_blocks', $blocks );
	}

	/**
	 * @param bool|string[]            $allowed Current allowed list (true = all).
	 * @param \WP_Block_Editor_Context $context Editor context.
	 * @return bool|string[]
	 */
	public static function allowed_blocks( $allowed, $context ) {
		if ( empty( $context->post ) || Guide_Post_Type::POST_TYPE !== $context->post->post_type ) {
			return $allowed;
		}
		return self::allowed_block_list();
	}

	public static function editor_assets(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || Guide_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$js  = FBHI_GUIDES_ASSETS_DIR . '/guides-editor.js';
		$css = FBHI_GUIDES_ASSETS_DIR . '/guides-editor.css';

		wp_enqueue_script(
			'fbhi-guides-editor',
			FBHI_GUIDES_ASSETS_URL . '/guides-editor.js',
			array( 'wp-plugins', 'wp-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-compose' ),
			file_exists( $js ) ? (string) filemtime( $js ) : FBHI_GUIDES_VERSION,
			true
		);
		wp_set_script_translations( 'fbhi-guides-editor', 'salient-child', get_stylesheet_directory() . '/languages' );

		$palette = array_map(
			static fn( array $entry ): array => array( 'name' => $entry['name'], 'color' => $entry['color'] ),
			Guide_Meta::palette()
		);
		wp_add_inline_script(
			'fbhi-guides-editor',
			'window.fbhiGuideEditor = ' . wp_json_encode( array(
				'postType'  => Guide_Post_Type::POST_TYPE,
				'metaAccent'=> Guide_Meta::ACCENT,
				'metaLabel' => Guide_Meta::LABEL,
				'palette'   => $palette,
			) ) . ';',
			'before'
		);

		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'fbhi-guides-editor',
				FBHI_GUIDES_ASSETS_URL . '/guides-editor.css',
				array(),
				(string) filemtime( $css )
			);
		}
	}

	/** Admin list: show label and accent so editors can check the structure at a glance. */
	public static function columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $title ) {
			$new[ $key ] = $title;
			if ( 'title' === $key ) {
				$new['fbhi_guide_label']  = __( 'Label', 'salient-child' );
				$new['fbhi_guide_accent'] = __( 'Colour', 'salient-child' );
			}
		}
		return $new;
	}

	public static function column_content( string $column, int $post_id ): void {
		if ( 'fbhi_guide_label' === $column ) {
			echo esc_html( Guide_Meta::label( $post_id ) );
			return;
		}
		if ( 'fbhi_guide_accent' === $column ) {
			$own    = Guide_Meta::own_accent( $post_id );
			$accent = Guide_Meta::accent_for( $post_id );
			if ( ! $accent ) {
				echo '&mdash;';
				return;
			}
			printf(
				'<span class="fbhi-guide-swatch" style="display:inline-block;width:1.2em;height:1.2em;vertical-align:middle;border-radius:3px;border:1px solid rgba(0,0,0,.15);background:%1$s" title="%1$s"></span> %2$s',
				esc_attr( $accent ),
				$own ? '' : esc_html__( '(inherited)', 'salient-child' )
			);
		}
	}
}
