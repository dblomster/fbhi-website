<?php
/**
 * Server render for fbhi/guide-index.
 *
 * @var array    $attributes Block attributes (heading).
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance (context: postId).
 *
 * @package Salient-Child
 */

use FBHI\Guides\Guide_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fbhi_block_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();

echo Guide_Blocks::render_index( (int) $fbhi_block_post_id, $attributes, get_block_wrapper_attributes( array( 'class' => 'fbhi-guide-index-block' ) ) );
