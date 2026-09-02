<?php
/**
 * "Last updated: <date>" from post_modified, locale-aware. Args: post.
 *
 * @package Salient-Child
 */

$fbhi_upd_post = $args['post'];
?>
<p class="fbhi-guide__updated">
	<strong><?php esc_html_e( 'Last updated:', 'salient-child' ); ?></strong>
	<time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $fbhi_upd_post ) ); ?>"><?php echo esc_html( get_the_modified_date( '', $fbhi_upd_post ) ); ?></time>
</p>
