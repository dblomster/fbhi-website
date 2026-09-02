<?php
/**
 * Guide page template (root index page and chapters), Salient framing.
 *
 * Content is rendered into a variable first so the TOC collector has run
 * before the sidebar prints. Never call the_content() here.
 *
 * @package Salient-Child
 */

use FBHI\Guides\Guide_Frontend;
use FBHI\Guides\Guide_Hierarchy;
use FBHI\Guides\Guide_Meta;
use FBHI\Guides\Guide_TOC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

the_post();
$fbhi_post    = get_post();
$fbhi_root    = Guide_Hierarchy::root( $fbhi_post->ID );
$fbhi_is_root = $fbhi_root && $fbhi_root->ID === $fbhi_post->ID;

Guide_TOC::start();
$fbhi_content = apply_filters( 'the_content', get_the_content() );
Guide_TOC::stop();
$fbhi_toc = Guide_TOC::items();
?>
<div class="container-wrap">
	<div class="container main-content" role="main">
		<div class="row">
			<?php nectar_hook_before_content(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'fbhi-guide' ); ?> style="<?php echo esc_attr( Guide_Meta::inline_style( $fbhi_post->ID ) ); ?>">

				<aside class="fbhi-guide__sidebar">
					<?php
					Guide_Frontend::part( 'sidebar-nav', array(
						'post'    => $fbhi_post,
						'root'    => $fbhi_root,
						'toc'     => $fbhi_toc,
					) );
					?>
				</aside>

				<div class="fbhi-guide__main">
					<?php
					Guide_Frontend::part( 'chapter-header', array(
						'post'    => $fbhi_post,
						'is_root' => $fbhi_is_root,
					) );
					?>

					<div class="fbhi-guide__content entry-content">
						<?php echo $fbhi_content; // Already filtered through the_content. ?>
					</div>

					<?php
					Guide_Frontend::part( 'last-updated', array( 'post' => $fbhi_post ) );
					Guide_Frontend::part( 'bottom-nav', array( 'post' => $fbhi_post, 'root' => $fbhi_root ) );
					?>
				</div>
			</article>

			<?php nectar_hook_after_content(); ?>
		</div>
	</div>
	<?php nectar_hook_before_container_wrap_close(); ?>
</div>
<?php
get_footer();
