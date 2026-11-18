# Kommuner in Swedish alphabetical order (Å, Ä, Ö) — 2026-11

**Status (2026-11-18):** Swedish sort **live on dev + prod** (Daniel's OK; checked on prod: grid Bjuv → Sollentuna in the
first 9, prev/next Malmö ↔ Lund/Sollentuna, Värnamo → Umeå/Åstorp). Follow-up "Load more" on the grid (Daniel's decision,
like /sv/forskningsprojekt/) + Swedish button text "Visa fler": the **text filter is on dev**, prod deploy and the
page-14055 setting change are **pending** (the prod deploy was blocked by the permission check; waiting for Daniel).
Item 1 of Annika's email of 2026-11-18
(the reply covers item 1 and item 2, the MailPoet footer links: [mailpoet-unsubscribe-manage/](mailpoet-unsubscribe-manage/README.md)).

## Annika's request

> Kan du vara snäll och fixa så att kommunernas namn kommer i rätt bokstavsordning här:
> https://fbhi.se/sv/inspirerande-exempel/ Dvs att Å, Ä, Ö inte behandlas som A resp O?

## Cause

- Page 14055 (`/sv/inspirerande-exempel/`) lists the `kommuner` posts with Salient's **Post Grid** (`nectar_post_grid`,
  `post_type="custom" cpt_name="kommuner" orderby="title" order="ASC"`). That is a plain `WP_Query` → `ORDER BY
  wp_…posts.post_title ASC`.
- `post_title` is collated **`utf8mb4_unicode_520_ci` on both prod (MariaDB 10.11.19) and dev (MariaDB 11.4.13)**.
  Unicode collation treats Å/Ä as A and Ö as O, so "Åstorps kommun" came first and "Östersunds kommun" sat between
  Malmö and Sollentuna. The two servers don't differ on this. Swedish order needs `utf8mb4_swedish_ci`.
- The kommun singles' prev/next links walk by title too (`'adjacent_order' => 'title'`, `functions.php`), with
  the same problem.

## Fix (child theme, `functions.php`)

- Registry `fbhi_blog_like_cpts()`: `'title_collation' => 'utf8mb4_swedish_ci'` for `kommuner`.
- New section "Title collation for blog-like CPTs": `fbhi_title_collate_sql()` and a `posts_orderby` filter
  (`fbhi_title_orderby_collation`) that adds `COLLATE utf8mb4_swedish_ci` after `…posts.post_title` in every single-post-type
  query for that CPT. That covers the Post Grid on page load and its AJAX category filter (`salient-core/includes/post-grid/post-grid.php`,
  also a plain `WP_Query`).
- The prev/next WHERE comparison and ORDER BY use the same collation.
- Network projects are unchanged (no `title_collation`; mostly English names).
- No database change (no `ALTER TABLE`); revert = remove the registry key (or the section).

## Verification

- **Prod data, read-only** (same `WP_Query` with the COLLATE added via a one-request filter, before deploy):
  before: Åstorp, Bjuv, Bollnäs, Göteborg, Huddinge, Kramfors, Lidingö, Lund, Malmö, Östersund, Sollentuna, Umeå, Värnamo;
  after: Bjuv, Bollnäs, Göteborg, Huddinge, Kramfors, Lidingö, Lund, Malmö, Sollentuna, Umeå, Värnamo, **Åstorp, Östersund**.
- **Dev:** the generated SQL is `ORDER BY …post_title COLLATE utf8mb4_swedish_ci ASC` for kommuner, unchanged for
  network projects; prev/next still works. Dev **can't** show the Swedish order (see below) and only has 2 kommuner.

## Found on the way: dev's database text is double-encoded

Dev's `wp-config.php` has `DB_CHARSET` = `latin1` (prod: `utf8`, which WordPress upgrades to `utf8mb4`). The dev connection
is therefore latin1, and text is stored **double-encoded** in the utf8mb4 tables ("å" = `C3 83 C2 A5` instead of `C3 A5`).
Pages look right because the round trip reverses it, but MySQL sorts and compares the wrong characters on dev, and
`$wpdb` rejects queries with literal non-latin1 characters ("contains invalid data"). Not fixed (dev only; a proper fix
is a charset conversion of the dev DB or a fresh prod → dev copy with `DB_CHARSET` `utf8mb4`). Keep in mind when testing
anything text-sorting or search-related on dev.

## Also noticed: the grid only shows 9 of 13 kommuner

The Post Grid has `posts_per_page="9"` with `pagination="none"`, so no load-more button is rendered. 4 of the 13 kommuner
never show on the page: today Östersund, Sollentuna, Umeå, Värnamo; after the fix Umeå, Värnamo, Åstorp, Östersund
(Åstorp is password-protected, shown as "Granskas: Åstorps kommun").

**Decision (Daniel):** use "Load more" like `/sv/forskningsprojekt/` (page 9807, sv translation of 7360: `pagination="load-more"`,
`posts_per_page="9"`). Plan:
1. Page 14055: `pagination="none"` → `pagination="load-more"` in its `nectar_post_grid` shortcode (one attribute, a
   revision is kept). Equivalent in WPBakery: Post Grid → Pagination → Load More.
2. The button text is `esc_html__( 'Load More', 'salient-core' )` in `data-load-more-text` (Salient's JS builds the
   button from it). salient-core has no Swedish translation, so the English text also shows on /sv/forskningsprojekt/.
   Fixed with a `gettext` filter in `functions.php` (section "Salient Post Grid: Swedish 'Load More' button"):
   "Visa fler" on Swedish pages. Checked on dev: `/sv/forskningsprojekt/` → `Visa fler`, `/network-projects/` →
   `Load More`.
