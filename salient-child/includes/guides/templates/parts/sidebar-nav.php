<?php
/**
 * Guide navigation: Start + chapters, each expandable to its H2 headings.
 * The current page's chapter is open and gets the live TOC; other chapters
 * get their headings parsed from their content. Wrapped in a <details> that
 * collapses to a dropdown on small screens (guides.js toggles `open`).
 *
 * Args: post (WP_Post), root (?WP_Post), toc (array of id/text/level).
 *
 * @package Salient-Child
 */

use FBHI\Guides\Guide_Hierarchy;
use FBHI\Guides\Guide_Meta;
use FBHI\Guides\Guide_TOC;

$fbhi_nav_post = $args['post'];
$fbhi_nav_root = $args['root'];
if ( ! $fbhi_nav_root ) {
	return;
}
$fbhi_nav_chapters   = Guide_Hierarchy::children( $fbhi_nav_root->ID );
$fbhi_nav_current_ch = Guide_Hierarchy::chapter_of( $fbhi_nav_post->ID );
$fbhi_nav_is_root    = $fbhi_nav_root->ID === $fbhi_nav_post->ID;

$fbhi_nav_current_title = $fbhi_nav_is_root
	? __( 'Start', 'salient-child' )
	: trim( Guide_Meta::label( $fbhi_nav_post->ID ) . ' ' . get_the_title( $fbhi_nav_post ) );
?>
<nav class="fbhi-guide-nav" aria-label="<?php esc_attr_e( 'Guide navigation', 'salient-child' ); ?>">
	<details class="fbhi-guide-nav__wrap" open>
		<summary class="fbhi-guide-nav__summary">
			<span class="fbhi-guide-nav__summary-guide"><?php echo esc_html( get_the_title( $fbhi_nav_root ) ); ?></span>
			<span class="fbhi-guide-nav__summary-current"><?php echo esc_html( $fbhi_nav_current_title ); ?></span>
		</summary>

		<p class="fbhi-guide-nav__guide-title"><?php echo esc_html( get_the_title( $fbhi_nav_root ) ); ?></p>

		<ol class="fbhi-guide-nav__list">
			<li class="fbhi-guide-nav__item fbhi-guide-nav__item--start<?php echo $fbhi_nav_is_root ? ' is-current' : ''; ?>">
				<a class="fbhi-guide-nav__link" href="<?php echo esc_url( get_permalink( $fbhi_nav_root ) ); ?>"<?php echo $fbhi_nav_is_root ? ' aria-current="page"' : ''; ?>>
					<?php esc_html_e( 'Start', 'salient-child' ); ?>
				</a>
			</li>

			<?php foreach ( $fbhi_nav_chapters as $fbhi_ch ) :
				$fbhi_is_current_ch = $fbhi_nav_current_ch && $fbhi_nav_current_ch->ID === $fbhi_ch->ID;
				$fbhi_is_page       = $fbhi_ch->ID === $fbhi_nav_post->ID;
				$fbhi_ch_label      = Guide_Meta::label( $fbhi_ch->ID );
				$fbhi_ch_style      = Guide_Meta::inline_style( $fbhi_ch->ID );
				$fbhi_ch_headings   = $fbhi_is_page ? $args['toc'] : Guide_TOC::items_for_post( $fbhi_ch );
				?>
				<li class="fbhi-guide-nav__item fbhi-guide-nav__item--chapter<?php echo $fbhi_is_current_ch ? ' is-current' : ''; ?>" style="<?php echo esc_attr( $fbhi_ch_style ); ?>">
					<details class="fbhi-guide-nav__chapter"<?php echo $fbhi_is_current_ch ? ' open' : ''; ?>>
						<summary class="fbhi-guide-nav__chapter-summary">
							<a class="fbhi-guide-nav__link" href="<?php echo esc_url( get_permalink( $fbhi_ch ) ); ?>"<?php echo $fbhi_is_page ? ' aria-current="page"' : ''; ?>>
								<?php if ( $fbhi_ch_label ) : ?><span class="fbhi-guide-nav__label"><?php echo esc_html( $fbhi_ch_label ); ?></span><?php endif; ?>
								<span class="fbhi-guide-nav__title"><?php echo esc_html( get_the_title( $fbhi_ch ) ); ?></span>
							</a>
							<span class="fbhi-guide-nav__chevron" aria-hidden="true"></span>
						</summary>
						<?php if ( $fbhi_ch_headings ) : ?>
							<ol class="fbhi-guide-toc" aria-label="<?php esc_attr_e( 'Contents', 'salient-child' ); ?>">
								<?php foreach ( $fbhi_ch_headings as $fbhi_h ) : ?>
									<li class="fbhi-guide-toc__item fbhi-guide-toc__item--h<?php echo (int) $fbhi_h['level']; ?>">
										<a class="fbhi-guide-toc__link" href="<?php echo esc_url( get_permalink( $fbhi_ch ) . '#' . $fbhi_h['id'] ); ?>" data-toc-target="<?php echo $fbhi_is_page ? esc_attr( $fbhi_h['id'] ) : ''; ?>">
											<?php echo esc_html( $fbhi_h['text'] ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ol>
						<?php endif; ?>
					</details>
				</li>
			<?php endforeach; ?>
		</ol>
	</details>
</nav>
