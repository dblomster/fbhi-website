<?php
/**
 * Automatic table of contents: gives headings stable ids while the content
 * renders and collects them for the sidebar. Ids come from the block's own
 * anchor when the editor set one, otherwise from the heading text.
 *
 * Usage: Guide_TOC::start(); $html = apply_filters( 'the_content', … );
 *        $items = Guide_TOC::items(); Guide_TOC::stop();
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_TOC {

	private static bool $collecting = false;

	/** @var array<int, array{id:string, text:string, level:int}> */
	private static array $items = array();

	/** @var array<string, int> */
	private static array $used_ids = array();

	public static function register(): void {
		add_filter( 'render_block_core/heading', array( __CLASS__, 'process_heading' ), 10, 2 );
	}

	/** Deepest heading level included in the TOC (2 = H2 only). */
	public static function max_level(): int {
		return (int) apply_filters( 'fbhi_guide_toc_max_level', 2 );
	}

	public static function start(): void {
		self::$collecting = true;
		self::$items      = array();
		self::$used_ids   = array();
	}

	public static function stop(): void {
		self::$collecting = false;
	}

	/** @return array<int, array{id:string, text:string, level:int}> */
	public static function items(): array {
		return self::$items;
	}

	/**
	 * @param string $html  Rendered heading block.
	 * @param array  $block Parsed block (attrs: level, anchor).
	 */
	public static function process_heading( string $html, array $block ): string {
		if ( ! self::$collecting ) {
			return $html;
		}
		$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;
		if ( $level < 2 || $level > self::max_level() ) {
			return $html;
		}
		if ( ! preg_match( '/<h' . $level . '\b([^>]*)>(.*?)<\/h' . $level . '>/is', $html, $m ) ) {
			return $html;
		}
		$attrs = $m[1];
		$text  = trim( wp_strip_all_tags( $m[2] ) );
		if ( '' === $text ) {
			return $html;
		}

		$id = '';
		if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attrs, $idm ) ) {
			$id = $idm[2];
		}
		if ( '' === $id ) {
			$id   = self::unique_id( sanitize_title( $text ) ?: 'section' );
			$html = preg_replace( '/<h' . $level . '\b/i', '<h' . $level . ' id="' . esc_attr( $id ) . '"', $html, 1 );
		} else {
			self::$used_ids[ $id ] = 1;
		}

		self::$items[] = array( 'id' => $id, 'text' => $text, 'level' => $level );
		return $html;
	}


	/**
	 * Headings of another guide page, parsed from its blocks (not rendered),
	 * with the same id algorithm as process_heading() so links match.
	 *
	 * @return array<int, array{id:string, text:string, level:int}>
	 */
	public static function items_for_post( \WP_Post $post ): array {
		$items = array();
		$used  = array();
		$walk  = static function ( array $blocks ) use ( &$walk, &$items, &$used ): void {
			foreach ( $blocks as $block ) {
				if ( 'core/heading' === ( $block['blockName'] ?? '' ) ) {
					$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;
					if ( $level >= 2 && $level <= self::max_level() ) {
						$text = trim( wp_strip_all_tags( $block['innerHTML'] ?? '' ) );
						if ( '' !== $text ) {
							$id = $block['attrs']['anchor'] ?? '';
							if ( '' === $id ) {
								$base = sanitize_title( $text ) ?: 'section';
								$id   = $base;
								$n    = 1;
								while ( isset( $used[ $id ] ) ) {
									$id = $base . '-' . ( ++$n );
								}
							}
							$used[ $id ] = 1;
							$items[]     = array( 'id' => $id, 'text' => $text, 'level' => $level );
						}
					}
				}
				if ( ! empty( $block['innerBlocks'] ) ) {
					$walk( $block['innerBlocks'] );
				}
			}
		};
		$walk( parse_blocks( $post->post_content ) );
		return $items;
	}

	private static function unique_id( string $base ): string {
		$id = $base;
		$n  = 1;
		while ( isset( self::$used_ids[ $id ] ) ) {
			$id = $base . '-' . ( ++$n );
		}
		self::$used_ids[ $id ] = 1;
		return $id;
	}
}
