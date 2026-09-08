<?php
/**
 * Guide post meta: accent colour, chapter label and print-button label, plus the FBHI palette
 * and accent inheritance (chapter → nearest ancestor → guide root).
 *
 * Keys are underscore-prefixed so they stay out of the generic Custom Fields
 * panel; auth_callback makes them editable through the REST API (Gutenberg).
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_Meta {

	public const ACCENT = '_fbhi_guide_accent';
	public const LABEL  = '_fbhi_guide_label';
	public const PRINT_LABEL = '_fbhi_guide_print_label';

	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
	}

	public static function register_meta(): void {
		$auth = static function ( bool $allowed, string $meta_key, int $post_id ): bool {
			return current_user_can( 'edit_post', $post_id );
		};

		register_post_meta(
			Guide_Post_Type::POST_TYPE,
			self::ACCENT,
			array(
				'type'              => 'string',
				'description'       => __( 'Chapter accent colour (hex).', 'salient-child' ),
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_hex' ),
				'auth_callback'     => $auth,
				'revisions_enabled' => false,
			)
		);

		register_post_meta(
			Guide_Post_Type::POST_TYPE,
			self::LABEL,
			array(
				'type'              => 'string',
				'description'       => __( 'Chapter designation shown above the title, e.g. "Avsnitt 1".', 'salient-child' ),
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
				'revisions_enabled' => false,
			)
		);

		register_post_meta(
			Guide_Post_Type::POST_TYPE,
			self::PRINT_LABEL,
			array(
				'type'              => 'string',
				'description'       => __( 'Text of the print button. Empty means the default.', 'salient-child' ),
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
				'revisions_enabled' => false,
			)
		);
	}

	/** Default text of the print button when a page sets none. */
	public static function default_print_label(): string {
		return __( 'Print', 'salient-child' );
	}

	public static function sanitize_hex( $value ): string {
		$hex = sanitize_hex_color( (string) $value );
		return $hex ? strtoupper( $hex ) : '';
	}

	/**
	 * FBHI graphic-manual accent colours. `color` is the accent, `tint` the
	 * manual's own lighter swatch used for card/header backgrounds.
	 *
	 * @return array<int, array{name:string, slug:string, color:string, tint:string}>
	 */
	public static function palette(): array {
		$palette = array(
			array( 'name' => __( 'Teal', 'salient-child' ),   'slug' => 'teal',   'color' => '#006885', 'tint' => '#BECCD8' ),
			array( 'name' => __( 'Green', 'salient-child' ),  'slug' => 'green',  'color' => '#90BB94', 'tint' => '#D6E4D5' ),
			array( 'name' => __( 'Yellow', 'salient-child' ), 'slug' => 'yellow', 'color' => '#FFE6AC', 'tint' => '#FFF3D8' ),
			array( 'name' => __( 'Peach', 'salient-child' ),  'slug' => 'peach',  'color' => '#E5A883', 'tint' => '#F5DDCD' ),
			array( 'name' => __( 'Navy', 'salient-child' ),   'slug' => 'navy',   'color' => '#213A6C', 'tint' => '#D3D8E2' ),
		);
		return apply_filters( 'fbhi_guide_palette', $palette );
	}

	/** Raw own value, no inheritance. */
	public static function own_accent( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::ACCENT, true );
	}

	public static function label( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::LABEL, true );
	}

	/** Print-button text for a page: its own value, else the default. No inheritance. */
	public static function print_label( int $post_id ): string {
		$own = trim( (string) get_post_meta( $post_id, self::PRINT_LABEL, true ) );
		return '' !== $own ? $own : self::default_print_label();
	}

	/**
	 * Resolve the accent for a guide page: own value, else the nearest ancestor
	 * that has one (chapter → guide root). Empty string when nothing is set.
	 */
	public static function accent_for( int $post_id ): string {
		$own = self::own_accent( $post_id );
		if ( $own ) {
			return $own;
		}
		foreach ( get_post_ancestors( $post_id ) as $ancestor_id ) {
			$accent = self::own_accent( (int) $ancestor_id );
			if ( $accent ) {
				return $accent;
			}
		}
		return '';
	}

	/**
	 * Background tint for an accent: the palette's tint when the accent is a
	 * palette colour, otherwise a CSS color-mix so custom colours still work.
	 */
	public static function tint_for( string $accent ): string {
		if ( ! $accent ) {
			return '';
		}
		foreach ( self::palette() as $entry ) {
			if ( strcasecmp( $entry['color'], $accent ) === 0 ) {
				return $entry['tint'];
			}
		}
		return sprintf( 'color-mix(in srgb, %s 30%%, white)', $accent );
	}

	/**
	 * Inline style declaring the guide CSS custom properties for a post.
	 * Empty when no accent is set so theme defaults apply.
	 */
	public static function inline_style( int $post_id ): string {
		$accent = self::accent_for( $post_id );
		if ( ! $accent ) {
			return '';
		}
		return sprintf( '--fbhi-guide-accent:%s;--fbhi-guide-tint:%s;', $accent, self::tint_for( $accent ) );
	}
}
