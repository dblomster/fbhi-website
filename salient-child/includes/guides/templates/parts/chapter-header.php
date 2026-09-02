<?php
/**
 * Chapter / guide header: label, title, intro (manual excerpt), tinted band.
 * Args: post (WP_Post), is_root (bool).
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
	<?php if ( $fbhi_hdr_intro ) : ?>
		<div class="fbhi-guide-header__intro"><?php echo wpautop( $fbhi_hdr_intro ); ?></div>
	<?php endif; ?>
</header>
