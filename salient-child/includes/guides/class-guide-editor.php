<?php
/**
 * Editor experience for guides: curated block list, editor assets for the
 * "Guide page" settings panel (label, accent, print-button text), and admin list columns.
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
		add_action( 'enqueue_block_assets', array( __CLASS__, 'editor_content_styles' ) );
		add_action( 'init', array( __CLASS__, 'register_block_styles' ) );
		add_action( 'init', array( __CLASS__, 'register_patterns' ) );
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
			static fn( array $entry ): array => array( 'name' => $entry['name'], 'color' => $entry['color'], 'tint' => $entry['tint'] ),
			Guide_Meta::palette()
		);
		wp_add_inline_script(
			'fbhi-guides-editor',
			'window.fbhiGuideEditor = ' . wp_json_encode( array(
				'postType'  => Guide_Post_Type::POST_TYPE,
				'metaAccent'=> Guide_Meta::ACCENT,
				'metaLabel' => Guide_Meta::LABEL,
				'metaPrintLabel'    => Guide_Meta::PRINT_LABEL,
				'printLabelDefault' => Guide_Meta::default_print_label(),
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

	/**
	 * Block styles: presentation variants of core blocks, styled in guides.css
	 * via .is-style-fbhi-* classes. Registered globally but only reachable on
	 * guides since the blocks are curated per post type.
	 */
	public static function register_block_styles(): void {
		register_block_style( 'core/group', array(
			'name'  => 'fbhi-infobox',
			'label' => __( 'Info box', 'salient-child' ),
		) );
		register_block_style( 'core/group', array(
			'name'  => 'fbhi-callout',
			'label' => __( 'Callout (chapter colour)', 'salient-child' ),
		) );
		register_block_style( 'core/group', array(
			'name'  => 'fbhi-checklist',
			'label' => __( 'Checklist', 'salient-child' ),
		) );
		register_block_style( 'core/separator', array(
			'name'  => 'fbhi-pagebreak',
			'label' => __( 'Print page break', 'salient-child' ),
		) );
	}

	/** Pre-assembled core-block patterns for the guide components. */
	public static function register_patterns(): void {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}
		register_block_pattern_category( 'fbhi-guide', array(
			'label' => __( 'FBHI Guide', 'salient-child' ),
		) );

		$post_types = array( Guide_Post_Type::POST_TYPE );

		register_block_pattern( 'fbhi/guide-infobox', array(
			'title'       => __( 'Info box', 'salient-child' ),
			'description' => __( 'A neutral box for background facts, tools or tips.', 'salient-child' ),
			'categories'  => array( 'fbhi-guide' ),
			'postTypes'   => $post_types,
			'content'     => '<!-- wp:group {"className":"is-style-fbhi-infobox","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-fbhi-infobox"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Good to know', 'salient-child' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Write the box text here.', 'salient-child' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
		) );

		register_block_pattern( 'fbhi/guide-callout', array(
			'title'       => __( 'Callout with questions', 'salient-child' ),
			'description' => __( 'A box in the chapter colour, for reflection questions such as "Fundera på:".', 'salient-child' ),
			'categories'  => array( 'fbhi-guide' ),
			'postTypes'   => $post_types,
			'content'     => '<!-- wp:group {"className":"is-style-fbhi-callout","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-fbhi-callout"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Think about:', 'salient-child' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>' . esc_html__( 'First question?', 'salient-child' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Second question?', 'salient-child' ) . '</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->',
		) );

		register_block_pattern( 'fbhi/guide-checklist', array(
			'title'       => __( 'Checklist', 'salient-child' ),
			'description' => __( 'Numbered checklist box, typically at the end of a chapter.', 'salient-child' ),
			'categories'  => array( 'fbhi-guide' ),
			'postTypes'   => $post_types,
			'content'     => '<!-- wp:group {"className":"is-style-fbhi-checklist","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-fbhi-checklist"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Checklist', 'salient-child' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list"><!-- wp:list-item -->
<li>' . esc_html__( 'First item', 'salient-child' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Second item', 'salient-child' ) . '</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list --></div>
<!-- /wp:group -->',
		) );

		register_block_pattern( 'fbhi/guide-pagebreak', array(
			'title'       => __( 'Print page break', 'salient-child' ),
			'description' => __( 'Invisible on screen; starts a new page when printing.', 'salient-child' ),
			'categories'  => array( 'fbhi-guide' ),
			'postTypes'   => $post_types,
			'content'     => '<!-- wp:separator {"className":"is-style-fbhi-pagebreak"} -->
<hr class="wp-block-separator has-alpha-channel-opacity is-style-fbhi-pagebreak"/>
<!-- /wp:separator -->',
		) );
	}

	/**
	 * Styles for the editor canvas (also inside the editor iframe): the same
	 * guides.css as the frontend plus editor-only tweaks. Guide screens only.
	 */
	public static function editor_content_styles(): void {
		if ( ! is_admin() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || Guide_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}
		$css = FBHI_GUIDES_ASSETS_DIR . '/guides.css';
		wp_enqueue_style( 'fbhi-guides', FBHI_GUIDES_ASSETS_URL . '/guides.css', array(), file_exists( $css ) ? (string) filemtime( $css ) : FBHI_GUIDES_VERSION );
		wp_enqueue_style( 'fbhi-guides-editor-font', 'https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700,900&display=swap', array(), null );
		$ecss = FBHI_GUIDES_ASSETS_DIR . '/guides-editor.css';
		wp_enqueue_style( 'fbhi-guides-editor-canvas', FBHI_GUIDES_ASSETS_URL . '/guides-editor.css', array( 'fbhi-guides' ), file_exists( $ecss ) ? (string) filemtime( $ecss ) : FBHI_GUIDES_VERSION );
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
