# CPT Previous/Next Navigation

## Goal

Make the bottom previous/next navigation (currently only on `network-project`) reusable for any custom post type.

## Broader context: adding a second CPT

This refactor is now motivated by a concrete need: a second CPT with the same feature set as `network-project` (same Salient blog-style single layout, same prev/next nav, possibly taxonomy, admin columns, etc.).

### What `network-project` currently ships

1. `register_post_type` + `network-project-category` taxonomy (`functions.php:27-71`)
2. `single-network-project.php` — copy of Salient's blog `single.php` plus one line calling the bottom-nav partial
3. `bottom-project-navigation.php` partial (this doc's subject)
4. WW-Fingers custom meta: admin column, quick edit, bulk edit, sortable, front-end display (`functions.php:73-400+`)
5. Conditional enqueue of Salient Portfolio's `portfolio.css` on single views (`functions.php:18-20`)

### Open question — must be answered before proceeding

Which of the above does the new CPT actually need? Specifically:

- [ ] Same Salient blog-style single layout?
- [ ] Bottom prev/next navigation?
- [ ] A category taxonomy (shared with network-project, or its own)?
- [ ] WW-Fingers meta field and admin UI? (This looks network-project-specific — worth confirming.)
- [ ] Custom admin columns / filters?
- [ ] What's the CPT slug, labels, and "back to all" archive URL?

The answer determines whether we copy-paste (fast, ~400 lines of near-duplication) or generalize further (more work upfront, cheap for CPT #3+).

### Recommended approach once answered

- **If WW-Fingers is network-project-only**: do the prev/next refactor described below, then copy-paste the ~30-line CPT registration block for the new CPT. The single-template itself is thin enough to duplicate.
- **If WW-Fingers (or other admin features) is shared**: also extract those into a reusable module that opts-in per CPT, before adding the second CPT.

## Current Implementation

### How it works

1. **Template** `single-network-project.php:174` includes the partial:
   ```php
   get_template_part('includes/partials/single-network-project/bottom-project-navigation');
   ```
2. **Partial** `includes/partials/single-network-project/bottom-project-navigation.php` uses WordPress's `previous_post_link()` / `next_post_link()` wrapped in HTML that reuses Salient Portfolio's CSS classes (`.bottom_controls`, `#portfolio-nav`, `#prev-link`, `#next-link`, `#all-items`).
3. **CSS** `functions.php:18-20` conditionally loads the Salient Portfolio plugin's `portfolio.css` on `network-project` single pages — this provides all the styling.
4. **"Back to all" link** is hardcoded to `/network-projects/`.

### Problems

- **Labels are hardcoded English**: "Previous Project" / "Next Project" and the title attribute "Back to all projects" are plain strings with no `__()` wrapper — WPML cannot translate them.
- **Labels say "Project"**: Would be wrong on any other CPT.
- **"Back to all" URL** is hardcoded to `/network-projects/`.

### Reference: Salient Portfolio's version

The Salient Portfolio plugin's implementation lives in:
- `reference/plugins/salient-portfolio/includes/frontend/helpers.php` → `nectar_project_single_controls()`
- `reference/plugins/salient-portfolio/includes/frontend/partials/bottom-project-navigation.php`
- `reference/plugins/salient-portfolio/css/portfolio.css` (section 2: Portfolio Controls, line ~393+)

It also hardcodes "Previous Project" / "Next Project" (with its own text domain `salient-portfolio`).

## Plan

### 1. Create a shared partial

Move the navigation to a shared location, e.g. `includes/partials/shared/bottom-post-navigation.php`, that accepts parameters:
- **"Back to all" URL** — archive page for the CPT
- Labels are generic: use WordPress core's `__( 'Previous', 'default' )` and `__( 'Next', 'default' )` which are already translated in all WP language packs (no custom WPML string registration needed).

### 2. Load portfolio CSS for new CPTs

Extend the `is_singular()` check in `functions.php:18` to include any CPT that uses this navigation:
```php
if ( is_singular( array( 'network-project', 'other-cpt' ) ) ) {
    wp_enqueue_style( 'portfolio', ... );
}
```

### 3. Include from each CPT's single template

Each `single-{cpt}.php` calls the shared partial with its specific "back to all" URL:
```php
get_template_part('includes/partials/shared/bottom-post-navigation', null, array(
    'archive_url' => site_url('/network-projects/'),
));
```
