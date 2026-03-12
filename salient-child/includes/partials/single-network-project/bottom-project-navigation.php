<?php
/**
 * Fallback bottom project navigation template
 *
 * This file is here only in case a legacy child theme calls it.
 * If your child theme is calling this from salient-child/single-portfolio.php,
 * please update your child theme to contain the actual file
 * (includes/partials/single-portfolio/bottom-project-navigation.php). The portfolio post
 * type is now contained in a plugin (Salient Portfolio) and not apart of the theme.
 *
 * @package Salient WordPress Theme
 * @version 10.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="bottom_controls">
	<div class="container">
		<div id="portfolio-nav">
			<ul>
				<li id="all-items">
					<a href="<?= site_url(); ?>/network-projects/" title="Back to all projects">
						<i class="icon-salient-back-to-all" style="position:relative"></i>
					</a>
				</li>
			</ul>
			<ul class="controls">
				<?php
					if (get_previous_post_link()) {
						echo '<li id="prev-link">';
							previous_post_link('%link', '<i class="fa fa-angle-left"></i> <span>Previous Project</span>');
						echo '</li>';
					}
				?>
				<?php
					if (get_next_post_link()) {
						echo '<li id="next-link">';
							next_post_link('%link', '<span>Next Project</span> <i class="fa fa-angle-right"></i>');
						echo '</li>';
					}
				?>
			</ul>
		</div>
	</div>
</div>
