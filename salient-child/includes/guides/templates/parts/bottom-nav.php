<?php
/**
 * Bottom navigation: previous / next page in reading order, back to the
 * guide start page, print button. Args: post, root.
 *
 * @package Salient-Child
 */

use FBHI\Guides\Guide_Hierarchy;
use FBHI\Guides\Guide_Meta;

$fbhi_bn_post = $args['post'];
$fbhi_bn_root = $args['root'];
$fbhi_bn_adj  = Guide_Hierarchy::adjacent( $fbhi_bn_post->ID );
$fbhi_bn_name = static function ( \WP_Post $p ) use ( $fbhi_bn_root ): string {
	if ( $fbhi_bn_root && $p->ID === $fbhi_bn_root->ID ) {
		return __( 'Start', 'salient-child' );
	}
	return trim( Guide_Meta::label( $p->ID ) . ' ' . get_the_title( $p ) );
};
?>
<div class="fbhi-guide__tools">
	<button type="button" class="fbhi-guide__print fbhi-guide-button fbhi-guide-button--ghost" data-guide-print>
		<?php esc_html_e( 'Print this page', 'salient-child' ); ?>
	</button>
</div>

<nav class="fbhi-guide-bottom-nav" aria-label="<?php esc_attr_e( 'Guide pages', 'salient-child' ); ?>">
	<?php if ( $fbhi_bn_adj['prev'] ) : ?>
		<a class="fbhi-guide-bottom-nav__link fbhi-guide-bottom-nav__link--prev" href="<?php echo esc_url( get_permalink( $fbhi_bn_adj['prev'] ) ); ?>" rel="prev">
			<span class="fbhi-guide-bottom-nav__dir"><?php esc_html_e( 'Previous', 'salient-child' ); ?></span>
			<span class="fbhi-guide-bottom-nav__name"><?php echo esc_html( $fbhi_bn_name( $fbhi_bn_adj['prev'] ) ); ?></span>
		</a>
	<?php else : ?>
		<span class="fbhi-guide-bottom-nav__spacer"></span>
	<?php endif; ?>

	<?php if ( $fbhi_bn_root && $fbhi_bn_root->ID !== $fbhi_bn_post->ID ) : ?>
		<a class="fbhi-guide-bottom-nav__link fbhi-guide-bottom-nav__link--up" href="<?php echo esc_url( get_permalink( $fbhi_bn_root ) ); ?>">
			<span class="fbhi-guide-bottom-nav__name"><?php esc_html_e( 'Back to the guide', 'salient-child' ); ?></span>
		</a>
	<?php endif; ?>

	<?php if ( $fbhi_bn_adj['next'] ) : ?>
		<a class="fbhi-guide-bottom-nav__link fbhi-guide-bottom-nav__link--next" href="<?php echo esc_url( get_permalink( $fbhi_bn_adj['next'] ) ); ?>" rel="next">
			<span class="fbhi-guide-bottom-nav__dir"><?php esc_html_e( 'Next', 'salient-child' ); ?></span>
			<span class="fbhi-guide-bottom-nav__name"><?php echo esc_html( $fbhi_bn_name( $fbhi_bn_adj['next'] ) ); ?></span>
		</a>
	<?php endif; ?>
</nav>
