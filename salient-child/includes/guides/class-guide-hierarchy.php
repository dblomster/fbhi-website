<?php
/**
 * Guide hierarchy helpers: root, chapters, depth-first walk, prev/next.
 * Pure data — no HTML. Queries respect WPML (it filters by current language).
 *
 * @package Salient-Child
 */

namespace FBHI\Guides;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Guide_Hierarchy {

	/** @var array<int, \WP_Post[]> Per-root cache of the flattened tree (request scope). */
	private static array $flat_cache = array();

	/** The top-level guide post for any guide page (itself when it is the root). */
	public static function root( int $post_id ): ?\WP_Post {
		$ancestors = get_post_ancestors( $post_id );
		$root_id   = $ancestors ? (int) end( $ancestors ) : $post_id;
		$root      = get_post( $root_id );
		return ( $root instanceof \WP_Post && Guide_Post_Type::POST_TYPE === $root->post_type ) ? $root : null;
	}

	public static function is_root( int $post_id ): bool {
		$post = get_post( $post_id );
		return $post instanceof \WP_Post && 0 === (int) $post->post_parent;
	}

	/** Direct children of a guide page, in menu_order then title. */
	public static function children( int $parent_id ): array {
		return get_posts( array(
			'post_type'        => Guide_Post_Type::POST_TYPE,
			'post_parent'      => $parent_id,
			'post_status'      => 'publish',
			'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'posts_per_page'   => -1,
			'no_found_rows'    => true,
			'suppress_filters' => false, // keep WPML language filtering.
		) );
	}

	/** Level-1 chapters of the guide a page belongs to. */
	public static function chapters( int $post_id ): array {
		$root = self::root( $post_id );
		return $root ? self::children( $root->ID ) : array();
	}

	/**
	 * Depth-first flatten of the whole guide (root excluded), used for
	 * prev/next so nested pages sit between their chapter and the next one.
	 *
	 * @return \WP_Post[]
	 */
	public static function flatten( int $root_id ): array {
		if ( isset( self::$flat_cache[ $root_id ] ) ) {
			return self::$flat_cache[ $root_id ];
		}
		$out  = array();
		$walk = static function ( int $parent_id ) use ( &$walk, &$out ): void {
			foreach ( self::children( $parent_id ) as $child ) {
				$out[] = $child;
				$walk( $child->ID );
			}
		};
		$walk( $root_id );
		self::$flat_cache[ $root_id ] = $out;
		return $out;
	}

	/**
	 * Previous and next page in reading order. The root counts as "previous"
	 * of the first chapter; the last page has no next.
	 *
	 * @return array{prev: ?\WP_Post, next: ?\WP_Post}
	 */
	public static function adjacent( int $post_id ): array {
		$root = self::root( $post_id );
		if ( ! $root ) {
			return array( 'prev' => null, 'next' => null );
		}
		$flat = self::flatten( $root->ID );
		if ( $post_id === $root->ID ) {
			return array( 'prev' => null, 'next' => $flat[0] ?? null );
		}
		$index = null;
		foreach ( $flat as $i => $post ) {
			if ( $post->ID === $post_id ) {
				$index = $i;
				break;
			}
		}
		if ( null === $index ) {
			return array( 'prev' => null, 'next' => null );
		}
		return array(
			'prev' => 0 === $index ? $root : $flat[ $index - 1 ],
			'next' => $flat[ $index + 1 ] ?? null,
		);
	}

	/** The level-1 chapter a page belongs to (itself when it is a chapter). Null for the root. */
	public static function chapter_of( int $post_id ): ?\WP_Post {
		$ancestors = get_post_ancestors( $post_id ); // nearest first.
		if ( ! $ancestors ) {
			return null; // root.
		}
		if ( 1 === count( $ancestors ) ) {
			return get_post( $post_id );
		}
		return get_post( (int) $ancestors[ count( $ancestors ) - 2 ] );
	}
}
