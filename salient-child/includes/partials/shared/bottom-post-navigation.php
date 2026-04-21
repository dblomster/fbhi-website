<?php
/**
 * Shared bottom previous/next post navigation.
 *
 * Reuses Salient Portfolio's CSS classes (.bottom_controls, #portfolio-nav,
 * #prev-link, #next-link, #all-items). The portfolio stylesheet must be
 * enqueued on the page for styling to apply — see fbhi_blog_like_cpts() in
 * functions.php.
 *
 * Args:
 *   - archive_url (string, required) — "Back to all" link target.
 *   - prev_label  (string, optional) — defaults to WP core __( 'Previous' ).
 *   - next_label  (string, optional) — defaults to WP core __( 'Next' ).
 *   - back_title  (string, optional) — title attribute on the "back" link.
 *
 * @package Salient-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$archive_url = isset( $args['archive_url'] ) ? $args['archive_url'] : home_url();
$prev_label  = isset( $args['prev_label'] )  ? $args['prev_label']  : __( 'Previous' );
$next_label  = isset( $args['next_label'] )  ? $args['next_label']  : __( 'Next' );
$back_title  = isset( $args['back_title'] )  ? $args['back_title']  : '';
?>

<div class="bottom_controls">
	<div class="container">
		<div id="portfolio-nav">
			<ul>
				<li id="all-items">
					<a href="<?php echo esc_url( $archive_url ); ?>" title="<?php echo esc_attr( $back_title ); ?>">
						<i class="icon-salient-back-to-all" style="position:relative"></i>
					</a>
				</li>
			</ul>
			<ul class="controls">
				<?php if ( get_previous_post_link() ) : ?>
					<li id="prev-link">
						<?php previous_post_link( '%link', '<i class="fa fa-angle-left"></i> <span>' . esc_html( $prev_label ) . '</span>' ); ?>
					</li>
				<?php endif; ?>
				<?php if ( get_next_post_link() ) : ?>
					<li id="next-link">
						<?php next_post_link( '%link', '<span>' . esc_html( $next_label ) . '</span> <i class="fa fa-angle-right"></i>' ); ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
