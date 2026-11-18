<?php
/**
 * DEMO (2026-11-18): image captions for Salient's "Image With Animation" WPBakery element.
 *
 * Adds a "Caption" field (plus a "Use Media Library caption" checkbox) to the element and prints the
 * caption below the image in the guide caption style
 * (grey, 14px, left-aligned, 7px under the image). Everything is limited to the demo page and its
 * WPML translations (FBHI_IMAGE_CAPTIONS_DEMO_PAGE): the field only appears when editing those
 * pages, captions are only printed there, and the CSS is only loaded there.
 *
 * To revert: delete the require line in functions.php, this file, css/image-captions-demo.css and
 * the image_with_animation entry in wpml-config.xml. See docs/wpbakery-image-captions/README.md.
 *
 * @package Salient-Child
 */

defined( 'ABSPATH' ) || exit;

/** Swedish demo page on dev ("Demo: Bildtexter under bilder"); its translations are included. */
const FBHI_IMAGE_CAPTIONS_DEMO_PAGE = 13972;

/**
 * IDs of the demo page and its WPML translations.
 *
 * @return int[]
 */
function fbhi_image_captions_demo_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids  = array( FBHI_IMAGE_CAPTIONS_DEMO_PAGE );
	$trid = apply_filters( 'wpml_element_trid', null, FBHI_IMAGE_CAPTIONS_DEMO_PAGE, 'post_page' );
	if ( $trid ) {
		$translations = apply_filters( 'wpml_get_element_translations', array(), $trid, 'post_page' );
		foreach ( (array) $translations as $translation ) {
			$ids[] = (int) $translation->element_id;
		}
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );
	return $ids;
}

/**
 * Whether the current request edits, previews or shows a demo page.
 *
 * Covers the frontend (queried page), WPBakery's backend editor (post.php?post=), its AJAX edit form
 * and shortcode previews (post_id) and the frontend editor (post_id / vc_post_id).
 *
 * @return bool
 */
function fbhi_image_captions_demo_active() {
	$ids = fbhi_image_captions_demo_ids();

	// Read-only lookups of the post being edited; nothing is saved from these values.
	// phpcs:disable WordPress.Security.NonceVerification
	foreach ( array( 'post', 'post_id', 'vc_post_id' ) as $key ) {
		if ( isset( $_REQUEST[ $key ] ) && in_array( absint( $_REQUEST[ $key ] ), $ids, true ) ) {
			return true;
		}
	}
	// phpcs:enable WordPress.Security.NonceVerification

	if ( did_action( 'wp' ) && in_array( (int) get_queried_object_id(), $ids, true ) ) {
		return true;
	}
	return false;
}

/**
 * "Caption" field on Image With Animation (General tab, last field), demo pages only.
 */
function fbhi_image_captions_demo_register_param() {
	if ( ! function_exists( 'vc_add_param' ) || ! fbhi_image_captions_demo_active() ) {
		return;
	}
	vc_add_param(
		'image_with_animation',
		array(
			'type'        => 'textarea',
			'heading'     => __( 'Caption', 'salient-child' ),
			'param_name'  => 'fbhi_caption',
			'admin_label' => true,
			'description' => __( 'Shown below the image: grey, small, left-aligned (same style as in the guides). Leave empty for no caption.', 'salient-child' ),
		)
	);
	vc_add_param(
		'image_with_animation',
		array(
			'type'        => 'checkbox',
			'heading'     => __( 'Use Media Library caption', 'salient-child' ),
			'param_name'  => 'fbhi_caption_media',
			'value'       => array( __( 'Yes, when the Caption field is empty', 'salient-child' ) => 'yes' ),
			'description' => __( 'Shows the caption saved on the image in the Media Library (in the page language). Text in the Caption field always wins.', 'salient-child' ),
		)
	);
}
add_action( 'vc_after_init', 'fbhi_image_captions_demo_register_param' );

/**
 * Prints the caption inside the element: Salient's ".inner" box becomes a <figure> holding the image
 * and a <figcaption>, so the caption follows the image's max width and alignment. If Salient's markup
 * ever changes, the caption is appended below the element instead.
 *
 * @param string       $output Shortcode output.
 * @param string       $tag    Shortcode tag.
 * @param array|string $attr   Shortcode attributes.
 * @return string
 */
function fbhi_image_captions_demo_render( $output, $tag, $attr ) {
	if ( 'image_with_animation' !== $tag || ! is_array( $attr ) || ! fbhi_image_captions_demo_active() ) {
		return $output;
	}

	$caption = fbhi_image_captions_demo_format( $attr['fbhi_caption'] ?? '' );
	if ( '' === $caption && 'yes' === ( $attr['fbhi_caption_media'] ?? '' ) && ! empty( $attr['image_url'] ) && ctype_digit( (string) $attr['image_url'] ) ) {
		// Same language switch as Salient's template: WPML Media keeps one attachment copy per language.
		$attachment_id = (int) apply_filters( 'wpml_object_id', (int) $attr['image_url'], 'attachment', true );
		$caption       = fbhi_image_captions_demo_format( (string) wp_get_attachment_caption( $attachment_id ) );
	}
	if ( '' === $caption ) {
		return $output;
	}
	$figcaption = '<figcaption class="fbhi-image-caption">' . $caption . '</figcaption>';

	$opened = 0;
	$closed = 0;
	$new    = preg_replace( '/<div class="inner"/', '<figure class="inner fbhi-image-caption__figure"', $output, 1, $opened );
	$new    = preg_replace( '#</div>(\s*)</div>(\s*)$#', $figcaption . '</figure>$1</div>$2', $new, 1, $closed );

	if ( 1 === $opened && 1 === $closed ) {
		return $new;
	}
	return $output . '<p class="fbhi-image-caption fbhi-image-caption--fallback">' . $caption . '</p>';
}
add_filter( 'do_shortcode_tag', 'fbhi_image_captions_demo_render', 10, 3 );

/**
 * Caption text as safe HTML: line breaks kept, links and emphasis allowed.
 *
 * WPBakery stores " [ ] in attributes as `` `{` `}` (vc_value_from_safe is for other param types).
 *
 * @param string $raw Attribute value.
 * @return string
 */
function fbhi_image_captions_demo_format( $raw ) {
	$text = str_replace( array( '``', '`{`', '`}`' ), array( '"', '[', ']' ), (string) $raw );
	// wpautop has already turned line breaks inside the shortcode into "<br />\n"; keep one break.
	$text = preg_replace( '#<br\s*/?>\s*(\r?\n)#i', '$1', $text );
	$text = wp_kses(
		trim( $text ),
		array(
			'a'      => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
			'em'     => array(),
			'strong' => array(),
			'i'      => array(),
			'b'      => array(),
			'br'     => array(),
		)
	);
	return nl2br( $text, false );
}

/**
 * Caption CSS, demo pages only (after custom.css).
 */
function fbhi_image_captions_demo_enqueue() {
	if ( ! fbhi_image_captions_demo_active() ) {
		return;
	}
	$file = get_stylesheet_directory() . '/css/image-captions-demo.css';
	wp_enqueue_style(
		'fbhi-image-captions-demo',
		get_stylesheet_directory_uri() . '/css/image-captions-demo.css',
		array( 'fbhi-custom' ),
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'fbhi_image_captions_demo_enqueue', 20 );
