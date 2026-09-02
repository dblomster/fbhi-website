<?php
/**
 * Guide index: one card per chapter, generated from the hierarchy.
 * Args: root (WP_Post), heading (string), wrapper (string: extra wrapper attributes from the block).
 *
 * @package Salient-Child
 */

use FBHI\Guides\Guide_Frontend;
use FBHI\Guides\Guide_Hierarchy;
use FBHI\Guides\Guide_Meta;

$fbhi_idx_chapters = Guide_Hierarchy::children( $args['root']->ID );
if ( ! $fbhi_idx_chapters ) {
	return;
}
$fbhi_idx_heading = $args['heading'] ?? __( 'Contents', 'salient-child' );
$fbhi_idx_wrapper = $args['wrapper'] ?? '';
?>
<div <?php echo $fbhi_idx_wrapper ? $fbhi_idx_wrapper : 'class="fbhi-guide-index-block"'; // phpcs:ignore WordPress.Security.EscapeOutput -- from get_block_wrapper_attributes(). ?>>
<section class="fbhi-guide-index" aria-label="<?php esc_attr_e( 'Chapters', 'salient-child' ); ?>">
	<?php if ( $fbhi_idx_heading ) : ?>
		<h2 class="fbhi-guide-index__heading"><?php echo esc_html( $fbhi_idx_heading ); ?></h2>
	<?php endif; ?>
	<ol class="fbhi-guide-index__list">
		<?php foreach ( $fbhi_idx_chapters as $fbhi_card ) :
			$fbhi_card_label = Guide_Meta::label( $fbhi_card->ID );
			$fbhi_card_intro = Guide_Frontend::intro( $fbhi_card );
			?>
			<li class="fbhi-guide-card" style="<?php echo esc_attr( Guide_Meta::inline_style( $fbhi_card->ID ) ); ?>">
				<?php if ( $fbhi_card_label ) : ?>
					<p class="fbhi-guide-card__label"><?php echo esc_html( $fbhi_card_label ); ?></p>
				<?php endif; ?>
				<h3 class="fbhi-guide-card__title">
					<a href="<?php echo esc_url( get_permalink( $fbhi_card ) ); ?>"><?php echo esc_html( get_the_title( $fbhi_card ) ); ?></a>
				</h3>
				<?php if ( $fbhi_card_intro ) : ?>
					<div class="fbhi-guide-card__intro"><?php echo wpautop( $fbhi_card_intro ); ?></div>
				<?php endif; ?>
				<a class="fbhi-guide-card__more fbhi-guide-button" href="<?php echo esc_url( get_permalink( $fbhi_card ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: chapter title */ __( 'Read more: %s', 'salient-child' ), get_the_title( $fbhi_card ) ) ); ?>">
					<?php esc_html_e( 'Read more', 'salient-child' ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
</div>
