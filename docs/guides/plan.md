# FBHI Guides — implementation plan

Companion to [decisions.md](decisions.md) (why) and [reference-site.md](reference-site.md) (what the
model site does). This file is the *how*. Update it as implementation deviates.

## 1. Content model

### Post type `guide`
- `register_post_type( 'guide' )`, **hierarchical**, `show_in_rest => true` (Gutenberg), `has_archive => false`.
- `supports`: `title`, `editor`, `excerpt`, `revisions`, `page-attributes` (gives Parent + Order UI in the editor),
  `custom-fields` (**required**: without it WP omits `meta` from the REST response and the Gutenberg panel
  cannot save; the underscore-prefixed keys keep the generic Custom Fields box empty).
  No `thumbnail` for now (no images in cards/header; can be added later).
- Rewrite: `slug => FBHI_GUIDE_SLUG` (constant, initial value `guide`), `with_front => false`,
  `hierarchical => true` so URLs nest: `/sv/guide/finger/steg-1/`. Changing the constant + flushing
  permalinks is the only step needed to rename the base.
- Not in WPBakery's post-type list (it only covers `post`/`page`), so guides are Gutenberg-only automatically.
- Levels: level 0 = guide root (index page), level 1 = chapters/steps, level 2+ allowed by the data model
  (navigation flattens depth-first) but not styled specially in release 1.

### Post meta (native `register_post_meta`, REST-exposed, `single => true`)
| Key | Type | On | Purpose |
|---|---|---|---|
| `fbhi_guide_accent` | hex string | chapters (root optional) | Accent colour. Sanitised with `sanitize_hex_color`. |
| `fbhi_guide_label` | string | chapters | Free-text designation shown above the title and on the card, e.g. `Steg 1`. |

Native **excerpt** = card description on the index **and** intro paragraph in the chapter header
(reference site uses identical text in both; see open question Q1).

**Accent resolution**: own meta → nearest ancestor with meta → guide root meta → theme default.
Implemented once in `Guide_Meta::accent_for( $post_id )`, exposed to CSS as `--fbhi-guide-accent`
on the article wrapper, so every guide element (card, header, sidebar, block styles) reads one variable.

**Palette** (from the FBHI graphic manual, see content-notes.md): teal `#006885`/tint `#BECCD8`, green
`#90BB94`/`#D6E4D5`, yellow `#FFE6AC`/`#FFF3D8`, peach `#E5A883`/`#F5DDCD`; primaries navy `#213A6C`, grey `#D5D3D3`.
Defined once in PHP
(`Guide_Meta::palette()`: `[ [ 'name' => …, 'slug' => …, 'color' => '#…' ], … ]`) and passed to the editor
panel as swatches. Editors pick from swatches; the picker still accepts a custom hex if needed.
It is *not* registered as the site-wide `editor-color-palette` to avoid affecting other post types.

### Editor sidebar panel
Hand-written (no build) `guides-editor.js` using `wp.plugins.registerPlugin` +
`PluginDocumentSettingPanel` (`wp.editor` / `wp.editPost` fallback) with `wp.element.createElement`:
- Label `TextControl`
- Accent `ColorPalette` with the FBHI swatches
- Reads/writes meta via `wp.data` `core/editor` `editPost({ meta })`.
Parent and Order come from the core "Page Attributes" panel — nothing custom.

## 2. Editor curation

- `allowed_block_types_all` filter, only when `post_type === 'guide'`:
  `core/paragraph, core/heading, core/list, core/list-item, core/image, core/gallery, core/video,
  core/embed (YouTube/Vimeo variations), core/buttons, core/button, core/quote, core/group,
  core/columns, core/column, core/table, core/file, core/separator, core/spacer, fbhi/guide-index`.
- **Block styles** (`register_block_style`, CSS in `guides.css` + editor stylesheet):
  | Block | Style | Renders as |
  |---|---|---|
  | `core/group` | Info box (`fbhi-infobox`) | neutral tinted box with padding, `break-inside: avoid` |
  | `core/group` | Callout (`fbhi-callout`) | accent-tinted box (uses `--fbhi-guide-accent`) |
  | `core/group` | Checklist (`fbhi-checklist`) | tinted box intended to wrap a heading + ordered list |
  | `core/separator` | Print page break (`fbhi-pagebreak`) | invisible on screen (small dashed marker in editor), `break-before: page` in print |
- **Block patterns** (`register_block_pattern`, category "FBHI Guide"): Info box, Callout, Checklist,
  Interview is deferred (no interviews in the draft; add when content needs it).
  Patterns are just pre-assembled core blocks with the styles above.
- Editor styles: `add_editor_style` scoped to guide via `enqueue_block_editor_assets` + post-type check
  so the editor previews boxes and the accent.

## 3. Frontend template

`single-guide.php` (theme root, thin) → `includes/guides/templates/single-guide.php`.

Salient framing kept: `get_header()`, `.container-wrap > .container.main-content > .row`, `get_footer()`.
**No** `nectar_page_header()` and no ACF hero (header.php's `get_field('hero_image')` simply finds no
value on guides; verify the ACF field-group location rules do not include the `guide` post type).

Layout inside the row (Salient grid classes for responsiveness):
```
<article class="fbhi-guide" style="--fbhi-guide-accent: …">
  <div class="col span_8 fbhi-guide__main">
    [chapter header]            templates/parts/chapter-header.php   (label, title, excerpt; tinted band)
    [content]                   pre-rendered $content (see TOC below)
    [index cards]               root only, auto-appended unless fbhi/guide-index block present
    [last updated]              templates/parts/last-updated.php
    [print button]
    [bottom nav]                templates/parts/bottom-nav.php        (prev / next / back to guide)
  </div>
  <aside class="col span_4 fbhi-guide__sidebar">
    [guide nav]                 templates/parts/sidebar-nav.php       (sticky; chapters + TOC)
  </aside>
</article>
```
Mobile: sidebar is visually moved above the chapter header (CSS `order` on a flex/grid wrapper) and
rendered as a single collapsed dropdown showing the current chapter name, as on the reference site.

### Content is rendered once, up front
The template calls `apply_filters( 'the_content', get_the_content() )` into a variable *before* output,
so the TOC collector has run and the sidebar can be printed anywhere in the DOM. Never call
`the_content()` twice.

### Automatic TOC (`Guide_TOC`)
- `render_block_core/heading` filter (only on singular guide; H2 always, H3 nested when the
  `fbhi_guide_toc_max_level` filter/constant says 3 — default 2):
  - if the block has an `anchor` attribute, use it;
  - else generate `sanitize_title( heading text )`, de-duplicated with `-2`, `-3`, and inject `id="…"`;
  - push `{ id, text, level }` into a collector.
- Sidebar reads the collector. Because ids are created at render time from the live heading text,
  nav and content cannot drift.
- H3 support is built in but off by default (D24); the sidebar nests H3 under its H2 when enabled.

### Sidebar nav (`templates/parts/sidebar-nav.php`)
Data from `Guide_Hierarchy`: root, ordered chapters (`menu_order`, then title), current post.
Markup: `<nav aria-label>` → "Start" link (root) → one `<details>` per chapter (`open` on current chapter)
→ current chapter's `<details>` contains the H2 list. Native `<details>` gives a working no-JS baseline.
`guides.js` adds: scroll-spy (IntersectionObserver → `.is-active` on the TOC item), and on mobile wraps
the whole nav in a collapsed `<details>` whose summary is the current chapter name.
Sticky: `position: sticky; top: var(--fbhi-guide-sticky-top)` — offset measured against Salient's header
height at implementation time (Salient sets header height variables/classes; verify on dev).

### Prev / next / back (`Guide_Hierarchy::adjacent()`)
Walk = depth-first flatten of the guide tree in `menu_order` (excluding root). Prev of first chapter = root.
Rendered by `templates/parts/bottom-nav.php` (guide-specific; the existing blog-like partial depends on
Salient Portfolio CSS and WP's date-based adjacency, so it is not reused). Exact visual is open (Q5):
Salient-style prev/next bar vs the reference's large "next chapter" teaser.

### Last updated
`templates/parts/last-updated.php`: `<strong>Senast uppdaterad:</strong> get_the_modified_date()`.
Label via `__()`, date via `get_option( 'date_format' )` so locale/WPML applies. Root page uses its own
modified date for now (Q2).

### Print
- `guides-print.css` enqueued with `media="print"` on guide singulars.
- Hides `#header-outer, #header-space, #footer-outer, .fbhi-guide__sidebar, .fbhi-guide__print,
  .fbhi-guide__bottom-nav, #to-top`, resets column widths to 100%, serif-free readable sizes, black text.
- `h2, h3 { break-after: avoid }`, boxes/figures/tables `{ break-inside: avoid }`, `.fbhi-pagebreak { break-before: page }`.
- Print button: `<button class="fbhi-guide__print">` → `window.print()` (label translatable). Prints the
  current page only.

### Site menu highlighting
`nav_menu_css_class` filter: on any guide post, the menu item whose object is the guide **root** gets
`current-menu-ancestor current_page_ancestor` (same idea as `fbhi_highlight_blog_like_cpt_menu_parent()`,
but resolved from the hierarchy instead of a registry). Daniel adds the root guide to the Swedish menu on dev.

## 4. Guide index block (`fbhi/guide-index`)

- Dynamic block, `block.json` + `render.php`, `register_block_type( __DIR__ . '/blocks/guide-index' )`.
  No attributes in release 1. Editor side (`editor.js`, hand-written): a placeholder box saying
  "Guide index — generated automatically from the chapters", plus `ServerSideRender` preview.
- `render.php`: chapters of the guide root (works when placed on a chapter too — always resolves to root),
  each card: label (small caps), title (link), excerpt, accent tint via inline `--fbhi-guide-accent`.
  Numeral circle as on the reference is Q3 (free-text label makes an automatic numeral unreliable).
- **Auto-append**: `the_content` filter on the guide root: if `! has_block( 'fbhi/guide-index' )`, append
  the block's rendered output after the content. Placing the block in the editor overrides the position.

## 5. WPML / i18n

- Child theme `wpml-config.xml`: `<custom-type translate="1">guide</custom-type>`,
  `<custom-field action="copy">fbhi_guide_accent</custom-field>`,
  `<custom-field action="translate">fbhi_guide_label</custom-field>`.
  WPML remaps `post_parent` to the translated parent when translating a hierarchical post, so the tree
  survives translation; `Guide_Hierarchy` always queries in the current language (WPML filters the query).
- Text domain `salient-child` (declared in `style.css`), `load_child_theme_textdomain()` from
  `salient-child/languages/`. Source strings in English (`Contents`, `Previous`, `Next`, `Back to the guide`,
  `Last updated:`, `Print this page`, …); Swedish in `languages/salient-child-sv_SE.po` + compiled `.mo`
  (compiled locally with `msgfmt` or `wp i18n make-mo`; the `.mo` is committed and deployed).
- Dates via `get_the_modified_date()` / `date_i18n` only. No hard-coded strings in templates.

## 6. File layout

```
salient-child/
├── functions.php                       (+1 line: require includes/guides/bootstrap.php)
├── single-guide.php                    (thin: require includes/guides/templates/single-guide.php)
├── wpml-config.xml
├── languages/salient-child-sv_SE.po|.mo
├── includes/guides/
│   ├── bootstrap.php                   requires + instantiates the classes below; FBHI_GUIDE_SLUG
│   ├── class-guide-post-type.php       CPT registration, rewrite, WPML-friendliness       (core logic)
│   ├── class-guide-meta.php            meta registration, palette, accent resolution     (core logic)
│   ├── class-guide-hierarchy.php       root / chapters / flatten / adjacent               (core logic, no HTML)
│   ├── class-guide-toc.php             heading ids + collector                             (core logic)
│   ├── class-guide-editor.php          allowed blocks, block styles, patterns, editor assets
│   ├── class-guide-blocks.php          registers fbhi/guide-index + auto-append filter
│   ├── class-guide-frontend.php        Salient-facing: enqueues, body class, menu highlight  (rendering)
│   ├── blocks/guide-index/             block.json, render.php, editor.js
│   └── templates/
│       ├── single-guide.php            (Salient markup)                                    (rendering)
│       └── parts/  chapter-header.php  sidebar-nav.php  index-cards.php  bottom-nav.php  last-updated.php
└── assets/guides/
    ├── guides.css          frontend (enqueued on guide singulars only, filemtime cache-bust)
    ├── guides-print.css    media="print"
    ├── guides-editor.css   editor preview of block styles
    ├── guides.js           scroll-spy, mobile dropdown, print button
    └── guides-editor.js    sidebar panel (label + accent), index block editor UI
```
"Core logic" files use only WordPress APIs and produce data, never Salient markup; a future plugin move
takes `includes/guides/` + `assets/guides/` wholesale and swaps `templates/` for its own.

CSS note: guide CSS lives in `assets/guides/` (module-local, conditionally loaded, has a print sheet)
instead of `css/custom.css`. It is still git-tracked and deployed by `upload.sh`, which is what the
`custom.css` rule in CLAUDE.md protects. CLAUDE.md is updated to state this exception.

## 7. Phases

| # | Phase | Deliverable / check on dev |
|---|---|---|
| 0 | Inputs | Colour scheme + draft content received; text domain + languages folder; `./upload.sh d` works |
| 1 ✅ | Post type + meta + editor | Deployed 2026-09-02. Verified via REST: type registered, hierarchy + meta saved. Editor panel/whitelist still to be eyeballed in wp-admin. |
| 2 | Frontend template | Chapter header, content, sticky sidebar with TOC, mobile dropdown, prev/next, last updated |
| 3 | Index block | Cards auto-appear on root; block placement override works |
| 4 | Components | Info box, callout, checklist, interview pattern, print page-break style |
| 5 | Print | Print stylesheet + button; page breaks honoured |
| 6 | i18n + menu | wpml-config, .po/.mo, menu highlighting; strings all translatable |
| 7 | Content | FINGER draft loaded on dev with Daniel via Novamira; review round |
| 8 | Prod | Only on explicit go from Daniel; purge Nginx cache after deploy |

## 8. QA checklist (dev)
- Desktop 1400 / tablet 900 / mobile 390: header, cards, sidebar, dropdown, bottom nav.
- Headings renamed in editor → TOC updates with no other action.
- Heading with custom anchor keeps that anchor.
- Chapter re-ordered via Order field → index, sidebar and prev/next all follow.
- Colour changed on chapter → card, header, sidebar and callouts follow; sub-page inherits.
- Root without index block → cards appended; with block → cards only where placed.
- Print preview: no chrome, sane widths, boxes not split, page-break style honoured.
- Swedish strings appear (Senast uppdaterad, Föregående, Nästa, Innehåll, Skriv ut).
- Non-guide pages unaffected (allowed-blocks filter and enqueues are guide-only).
