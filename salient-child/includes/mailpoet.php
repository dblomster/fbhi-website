<?php
/**
 * MailPoet: serve the subscription pages in Swedish.
 *
 * MailPoet builds every subscription link (the "Avsluta prenumeration" footer link, the "Ja, avregistrera mig"
 * button, the manage-subscription, resubscribe, sign-up confirmation and captcha pages) from get_permalink() of its
 * own `mailpoet_page` posts. Those posts are not translated by WPML, so the links carry no language and WPML serves
 * the pages in the default language (English). All FBHI newsletters are Swedish, so the links are switched to the
 * Swedish URL (/sv/…) here. MailPoet's own texts then render in Swedish through its sv_SE language pack.
 *
 * With click tracking on, MailPoet resolves these links when they are clicked, so already-sent newsletters get the
 * Swedish pages too.
 *
 * To revert: delete the require line in functions.php and this file. See docs/mailpoet-unsubscribe-manage/README.md.
 *
 * @package Salient-Child
 */

defined( 'ABSPATH' ) || exit;

/** WPML language code the MailPoet subscription pages are served in. */
const FBHI_MAILPOET_PAGE_LANGUAGE = 'sv';

/**
 * Point links to MailPoet's subscription pages at the Swedish version of the site.
 *
 * Runs after WPML's own permalink filters. Without WPML active, `wpml_permalink` has no callback and the link is
 * returned unchanged.
 *
 * @param string  $post_link The post's permalink.
 * @param WP_Post $post      The post in question.
 * @return string
 */
function fbhi_mailpoet_page_link_language( $post_link, $post ) {
	if ( ! $post instanceof WP_Post || 'mailpoet_page' !== $post->post_type ) {
		return $post_link;
	}

	return apply_filters( 'wpml_permalink', $post_link, FBHI_MAILPOET_PAGE_LANGUAGE );
}
add_filter( 'post_type_link', 'fbhi_mailpoet_page_link_language', 20, 2 );
