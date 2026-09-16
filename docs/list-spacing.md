# Body-text list spacing (ul/ol in WPBakery text columns)

**Status:** live on dev and prod since 2026-09-17. Pure CSS, easy to revert (see below).

## The problem

Salient's spacing around lists in running text was never tuned. Measured on
https://fbhi.se/sv/kommuner/umea-kommun/ before the change:

| Position                | Before | After | Guide CPT (for reference) |
|-------------------------|--------|-------|---------------------------|
| paragraph → list        | 24px   | 12px  | ~12px (.7em)              |
| list → next paragraph   | 10px   | 24px  | ~20px (1.2em)             |
| between list items      | 10px   | 10px  | ~7px (.4em) — not changed |

Why those numbers: paragraphs get `padding-bottom: 1.5em` (24px) from Salient's
material skin (`skin-material.css`), while a list only leaves the 10px `li`
margin (the `ul` margin of 10px collapses into it). So a list sat far from the
paragraph introducing it and glued to the paragraph after it. Lists that end a
text column were already fine — the column's own 24px margin takes over.

## The fix

`salient-child/css/custom.css`, group 1 "Theme overrides", section
"Body-text list spacing (WPBakery text columns)":

```css
.wpb_text_column p:has(+ ul, + ol) { padding-bottom: 0.75em; }  /* 24px → 12px */
.wpb_text_column ul + p,
.wpb_text_column ol + p { margin-top: 1.5em; }                   /* 10px → 24px */
```

- First rule shrinks the paragraph's own padding only when a list follows it.
- Second rule gives a following paragraph the normal paragraph gap; it is a
  margin, so it collapses with the list's existing 10px instead of adding to it.
- Scoped to `.wpb_text_column`: a crawl on 2026-09-17 showed **every** content
  list on the site (pages, posts, kommuner, network projects, both languages —
  308 lists on 80 URLs) lives in a WPBakery text column, so this reaches all
  editorial text and nothing else.
- `:has()` is supported by all current browsers. Fallback if ever needed:
  `.wpb_text_column p + ul, .wpb_text_column p + ol { margin-top: -0.75em; }`.

## What it touches / does not touch

Touched (paragraph directly before and/or after a list):

- ~92 lists that follow a paragraph, ~44 lists followed by a paragraph — mostly
  network projects, kommuner pages and news posts.
- The FAQ toggles on "Frågor och svar om FINGER" (8 lists): body text inside
  Salient toggles, treated as wanted.

Untouched by design:

- Kommun fact boxes (right column: H5 → list → H6) — headings, not paragraphs.
- Seminar programme pages (H4 → list).
- WPForms checkbox lists, prev/next post navigation, menus, sidebar widgets,
  footer, cookie banner, guides module (own CSS in `assets/guides/`).

## Deployment record

- Dev: full `./upload.sh d`.
- Prod: **custom.css only**, copied with a single-file rsync to
  `wp-content/themes/salient-child/css/custom.css` (md5 `c7c46c95…` on both
  sides). A full `./upload.sh p` would have shipped the guides module
  (functions.php, includes/guides/, assets/guides/, languages/, wpml-config.xml,
  single-guide.php), which is **not** cleared for prod yet. Until the guides go
  live, prod deploys of unrelated changes must stay file-targeted the same way.
- Verified on prod with a `?cb=` cache-buster: Umeå list 12px/24px, fact box
  unchanged (7px/10px). The uncached HTML already references the new
  Autoptimize file. Pages still held in the Nginx cache keep the old CSS until
  they expire or Nginx Helper purges them — no purge was run.

## Revert

Delete the "Body-text list spacing" block from `custom.css` and redeploy
(dev: `./upload.sh d`; prod: the same single-file rsync as above while the
guides module is still dev-only). Then purge the Nginx cache on prod.
