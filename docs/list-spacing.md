# Body-text list spacing (ul/ol in WPBakery text columns)

**Status:** live on dev and prod since 2026-09-17; list → heading rule added 2026-11-26 (see below). Pure CSS, easy to revert (see below).

## The problem

Salient's spacing around lists in running text was never tuned. Measured on
https://fbhi.se/sv/kommuner/umea-kommun/ before the change:

| Position                | Before | After | Guide CPT (for reference) |
|-------------------------|--------|-------|---------------------------|
| paragraph → list        | 24px   | 12px  | ~12px (.7em)              |
| list → next paragraph   | 10px   | 24px  | ~20px (1.2em)             |
| between ul items        | 10px   | 10px  | ~7px (.4em) — not changed |
| between ol items        | 0px    | 10px  | (fixed 2026-09-18)        |

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
.wpb_text_column ol li { margin-bottom: 10px; }                  /* 0 → 10px, added 2026-09-18 */
```

- First rule shrinks the paragraph's own padding only when a list follows it.
- Second rule gives a following paragraph the normal paragraph gap; it is a
  margin, so it collapses with the list's existing 10px instead of adding to it.
- Third rule (2026-09-18): Salient's base `style.css` gives `ul li` a 10px
  bottom margin but `ol li` only `list-style: decimal` — no margin at all — so
  numbered items were glued together (0px) while bullets had 10px. This was
  never caused by the 2026-09-17 rules, which only touch paragraphs; it was a
  pre-existing Salient gap that became noticeable once list spacing was looked
  at. Verified on prod by injecting an `<ol>` into the Umeå page (no live ol on
  that page): 0px → 10px between items, 24px to the following paragraph, ul and
  fact-box lists unchanged.
- Scoped to `.wpb_text_column`: a crawl on 2026-09-17 showed **every** content
  list on the site (pages, posts, kommuner, network projects, both languages —
  308 lists on 80 URLs) lives in a WPBakery text column, so this reaches all
  editorial text and nothing else.
- `:has()` is supported by all current browsers. Fallback if ever needed:
  `.wpb_text_column p + ul, .wpb_text_column p + ol { margin-top: -0.75em; }`.

## Heading straight after a list (2026-11-26)

Annika noticed that "En växande kunskapsbas" on
https://fbhi.se/sv/fran-global-forskning-till-implementering/ sat too close to
the bullet list above it. The September rules only covered a *paragraph* after a
list. Paragraph → heading is 34px (24px paragraph padding + the heading's 10px
top margin), but list → heading was 10px (list margin and heading margin
collapse, no padding in between).

```css
.wpb_text_column :is(ul, ol):has(+ :is(h2, h3, h4)) { margin-bottom: calc(1.5em + 10px); } /* 10px → 34px */
```

- The space goes on the list, not the heading: Salient's own rule
  `body.single .post .wpb_text_column h4 { margin-top: 10px }` out-specifies a
  margin set on the heading (tried, had no effect).
- Stops at h4 so the kommun fact boxes (list → H6, 12 of them) stay as they are.
- Sitemap crawl 2026-11-26 (275 URLs, 324 lists in text columns): a list
  directly followed by an h2–h4 exists in 5 places — Annika's page, the news
  post "New initiative for detection, prevention and treatment…" (sv + en), and
  the seminar programme pages `/seminarium-26-september/` and
  `/online-seminarium-28-november/` (H4 → list → H4, included by Daniel's
  decision). No list is followed by anything other than a paragraph, a heading
  or another list.
- Verified on dev at 1400px and in mobile emulation (390px): 34px on the news
  post and both seminar pages. Annika's page does not exist on dev; the same
  rule injected into the prod page in the browser gave 34px at both widths.
- **Content fix needed with it.** On the news post (sv 10618 / en 10638) the gap
  still looked ~57px on mobile: the stored heading was
  `<h4><strong>\r\nAbout the Innovative Health Initiative </strong></h4>` and
  `wpautop` turns that raw line break into a leading `<br>`, i.e. an empty line
  inside the heading. Before the rule, 10px + that hidden line happened to look
  right. Fixed on **dev** 2026-11-26 via Novamira execute-php (exact-string
  replace, `substr_count === 1` guard, `wp_update_post`; WPML languages kept),
  then on **prod** the same day, same procedure (Novamira request
  `listhead1126`, restorable via `novamira/list-changes`). WPML logged
  `gzuncompress(): data error` warnings from FieldCompression.php on both
  sites — not a dev-only quirk; the saves went through fine. sv 10618 is the
  WPML source, so the save flagged the en translation 10638 `needs_update = 1`
  (en already has the same fix, nothing to retranslate). Flag cleared on prod
  and dev the same day: `icl_translation_status.needs_update = 0` for
  translation_id 1286; the stored md5 already equalled
  `WPML_TM_Action_Helper::post_md5()` of 10618, so it stays cleared (prod
  Novamira request `wpmlflag1126`).
- A CSS fix for the `<br>` was rejected: `br:first-child` ignores text nodes, so
  it would also hide intended breaks like `Line one<br>line two`.
- Crawl 2026-11-26 for headings starting/ending with `<br>` or empty (prod):
  besides the news post, the kommun fact-box "Läs mer" H6s start with `<br>`
  (their spacing relies on it; the rule stops at h4 so they are untouched),
  five sv seminar pages have an H3 "FINGER-seminarium" ending in `<br>`, and
  there are empty `<h5></h5>`s on `/network-projects/`, `/network-projects-2/`,
  `/sv/forskningsprojekt/` and `/network-project/pensa-pensaplus-spain/`. Left
  as they are.
- Status: **live on dev and prod 2026-11-26.** Prod: `./upload.sh p` (only
  custom.css changed), heading fix as above, Nginx purge via
  `rt_nginx_helper_purge_all`. Verified on prod without cache-buster at 1400px
  and 390px mobile emulation: list → heading 34px on Annika's page, the news
  post (sv + en) and both seminar pages, every paragraph → heading still 34px;
  Umeå fact box (list → H6) unchanged at 10px, paragraph → list still 12px.
- Reply to Annika: drafted 2026-11-26 in
  `incoming/2026-11-26-svar-annika-avstand-efter-punktlista.md` (git-ignored);
  Daniel sends it, then mark it SENT here.

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

- 2026-09-18 (`ol li` rule): same procedure — `./upload.sh d` for dev, single-
  file rsync of `custom.css` to prod (md5 `d8ba7718…` on both sides). The
  uncached HTML references the new Autoptimize file; no Nginx purge was run.

## Revert

Delete the "Body-text list spacing" block from `custom.css` and redeploy
(dev: `./upload.sh d`; prod: the same single-file rsync as above while the
guides module is still dev-only). Then purge the Nginx cache on prod.
