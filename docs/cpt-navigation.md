# Blog-like CPTs: shared single template + prev/next navigation

## What this is

Multiple CPTs on this site (`network-project`, `kommuner`) use the Salient blog-style single-post layout plus a shared bottom prev/next navigation bar. This doc explains how the shared machinery is wired and how to add another CPT.

## Design

### 1. Central registry — `fbhi_blog_like_cpts()` in `functions.php`

Single source of truth:

```php
function fbhi_blog_like_cpts() {
    return array(
        'network-project' => array(
            'archive_page_id' => 7360, // /network-projects/ archive page
            'adjacent_order'  => 'title',
        ),
        'kommuner' => array(
            'archive_page_id' => 14055, // /sv/kommuner/ archive page
            'adjacent_order'  => 'title',
        ),
    );
}
```

The registry drives three things:
- **Conditional enqueue** of Salient Portfolio's `portfolio.css` on single views — the stylesheet provides the `.bottom_controls` / `#portfolio-nav` / `#prev-link` etc. classes the partial uses.
- **"Back to all" URL** in the shared bottom-nav partial, resolved at render time by `fbhi_get_blog_like_cpt_archive_url()`. The helper translates the configured page ID to the active language via WPML's `wpml_object_id` filter and returns `get_permalink()` for the result — so Swedish visitors land on the Swedish archive page even when the slug is translated (e.g. `/sv/forskningsprojekt/` for `network-project`). Falls back to `home_url()` if the page is missing; works unchanged when WPML is disabled.
- **Prev/next ordering** for the bottom nav (see below).

#### `adjacent_order` — prev/next walk order

WP core's `previous_post_link()` / `next_post_link()` always order by `post_date`. For CPTs whose archive listing is sorted alphabetically by title, that produces a confusing walk. Each CPT can opt into a different ordering:

- `'date'` (default, or key omitted) — WP core behavior; no SQL override.
- `'title'` — walk alphabetically by `post_title`.

Implementation lives in `functions.php` as `fbhi_adjacent_post_title_where()` / `fbhi_adjacent_post_title_sort()`, wired via the `get_{previous,next}_post_where` and `get_{previous,next}_post_sort` filters. Taxonomy / WPML language clauses added by other code are preserved — only the `post_date` comparison and `ORDER BY` are rewritten.

Archive pages themselves are manually-built WP pages at the given URLs (CPTs register with `has_archive => false`).

### 2. Shared single template — `includes/single-blog-like-cpt.php`

Contains the full Salient blog-style single-post body (a copy of Salient's blog `single.php`). At the end it calls the shared partial:

```php
get_template_part( 'includes/partials/shared/bottom-post-navigation', null, array(
    'archive_url' => fbhi_get_blog_like_cpt_archive_url( get_post_type() ),
) );
```

### 3. Per-CPT single templates at theme root

WP resolves `single-{post-type}.php` by filename, so each CPT still needs its own file at the theme root — but each is a thin `require`:

```php
// single-network-project.php, single-kommuner.php
<?php require get_stylesheet_directory() . '/includes/single-blog-like-cpt.php';
```

### 4. Shared partial — `includes/partials/shared/bottom-post-navigation.php`

Reuses Salient Portfolio's CSS classes. Accepts via `$args`:
- `archive_url` (required) — "back to all" target
- `prev_label` / `next_label` — default to WP core `__( 'Previous' )` / `__( 'Next' )` (translated in every WP language pack, so no custom WPML string registration needed)
- `back_title` — title attribute on the "back" link (optional)

## Adding another blog-like CPT

1. Register the CPT (and any taxonomy) in `functions.php` — copy an existing block as a template.
2. Build the archive page in WP admin (and its WPML translations, if any).
3. Add the CPT to `fbhi_blog_like_cpts()` with `archive_page_id` set to the WP post ID of that archive page. Find it in WP admin: edit the page and read `post=<id>` from the URL. Any language's ID works — WPML translates across the translation group at runtime.
4. Create `single-{cpt}.php` at the theme root as a one-liner that `require`s `includes/single-blog-like-cpt.php`.

If the new CPT needs admin features that are currently network-project-specific (WW-Fingers meta/column/bulk edit/frontend logo), extract those into a reusable module before wiring the new CPT — they are intentionally not part of this shared layer.
