<?php
/**
 * Chapter / guide header: label, title, intro (manual excerpt), tinted band, and a
 * print icon (same text as the bottom print button, screen only). Args: post (WP_Post), is_root (bool).
 *
 * @package Salient-Child
 */

use FBHI\Guides\Guide_Frontend;
use FBHI\Guides\Guide_Meta;

$fbhi_hdr_post  = $args['post'];
$fbhi_hdr_label = Guide_Meta::label( $fbhi_hdr_post->ID );
$fbhi_hdr_intro = Guide_Frontend::intro( $fbhi_hdr_post );
?>
<header class="fbhi-guide-header<?php echo $args['is_root'] ? ' fbhi-guide-header--root' : ''; ?>">
	<?php if ( $fbhi_hdr_label ) : ?>
		<p class="fbhi-guide-header__label"><?php echo esc_html( $fbhi_hdr_label ); ?></p>
	<?php endif; ?>
	<h1 class="fbhi-guide-header__title"><?php echo esc_html( get_the_title( $fbhi_hdr_post ) ); ?></h1>
	<button type="button" class="fbhi-guide-header__print" data-guide-print>
		<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
		<span class="fbhi-guide-header__print-tip"><?php echo esc_html( Guide_Meta::print_label( $fbhi_hdr_post->ID ) ); ?></span>
	</button>
	<?php if ( $fbhi_hdr_intro ) : ?>
		<div class="fbhi-guide-header__intro"><?php echo wpautop( $fbhi_hdr_intro ); ?></div>
	<?php endif; ?>
</header>
