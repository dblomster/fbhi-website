# Kommuner in Swedish alphabetical order (Å, Ä, Ö) — 2026-11

**Status: DONE on dev + prod 2026-11-18** (all with Daniel's OK). Verified on prod as a visitor:
`/sv/inspirerande-exempel/` shows **all 13** kommuner in Swedish order (Bjuv … Värnamo, "Granskas: Åstorps kommun",
Östersund), no button, console clean; prev/next Malmö → Lund/Sollentuna, Värnamo → Umeå/Åstorp.
`/sv/forskningsprojekt/` shows "Alla" and "Visa fler", 9 → 18 and then hides; English pages keep "All" / "Load More".
Reply to Annika (items 1 + 2) drafted in `incoming/2026-11-18-svar-annika-kommuner-mailpoet.md`.
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

**What was tried, and the outcome:**
1. First "Load more" like `/sv/forskningsprojekt/` (page 14055 `pagination="none"` → `"load-more"`). The button then
   **never hid** after all were loaded: Salient's JS (`init.js`, post grid) hides it when the number of cards reaches
   `data-total-count` of the active category filter link, and Salient only renders filter links when the grid has
   taxonomy terms (`nectar_post_grid.php:219`, needs `custom_query_tax`). `kommuner-category` has **no terms**, so the
   total was `parseInt(undefined)` = NaN. Cards loaded via the button also **lost the "Granskas:" prefix**: they come
   from `admin-ajax.php` (`nectar_get_post_grid_segment`), where `is_admin()` is true, and core's `get_the_title()`
   only adds the protected prefix outside admin.
2. **Final (Daniel's choice): show all, no button.** Page 14055's kommuner grid: `pagination="none"`,
   `posts_per_page="50"` (two attributes, `wp_update_post`, revisions kept). Equivalent in WPBakery: Post Grid →
   Pagination: None, Posts per page: 50. If kommuner ever exceed 50, raise the number (or give them categories and
   switch to load more).
3. Still useful, kept in `functions.php`: the "Granskas:"/"In review:" prefix for Post Grid AJAX cards (password section,
   `the_title` filter limited to `nectar_get_post_grid_segment`; covers the forskningsprojekt grid and category
   filters), and Swedish Post Grid texts via `gettext` (`salient-core`): "Load More" → "Visa fler", "All" → "Alla"
   (Swedish pages only).

**`/sv/forskningsprojekt/` was not broken.** It stops at 18 because Swedish has 18 published network projects (English
25); the 2 extra per language that a logged-in count shows are **private** posts.
