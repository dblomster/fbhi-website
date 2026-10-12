# Guide spacing and images (2026-10-12, D35 + D36)

Changes made after Annika's questions of 2026-10-12 (heading spacing, the foreword in columns, click to
enlarge, rounded corners and shadow, caption colour/alignment). Decided with Daniel the same day: follow the
behaviour of the rest of the site. **Status: dev only**; not yet on prod.

Every change is listed with its files so it can be reverted or adjusted on its own.

## D35: spacing

| # | Change | Before → after | Files |
|---|---|---|---|
| 1 | A subheading straight after a heading (H2 → H3, H3 → H4 …) sits close under it | 35px → 0.6em (13px under an H2) | `guides.css` (rule after `:is(h2…) + *`), `guides-editor.css` |
| 2 | A box (info/callout/checklist) straight after a heading sits close under it | 2em box margin (34px) → 0.8em (14px) | `guides.css` (after the box rule), `guides-editor.css` |
| 3 | Block rhythm inside columns and plain groups (same as top level: 1.2em, lists 0.7em, after a heading 0.6em) | frontend 0px between paragraphs in a column, editor 28px → 20px in both | `guides.css` ("Nested flow containers"), `guides-editor.css` |
| 4 | Images aligned left/right | text touching the image, 30px gap above the image → 1.6em air on the text side, image top level with the paragraph beside it, 1.6em below | `guides.css` ("Images aligned left/right"), `guides-editor.css` |
| 5 | Headings, boxes, figures, columns, files, quotes and buttons always start below a floated image; the content area ends below the last one | could sit beside or run under the image → always below (27px gap) | `guides.css` (`clear: both` list, `.fbhi-guide__content::after`), `guides-editor.css` |
| 6 | Phones (≤ 600px): floated images sit in the column like other figures and fill its width, keeping their aspect ratio (the width set in the editor is for the desktop layout beside the text; `!important` because WP puts it inline on the img) | a narrow image left a thin strip of text beside it | `guides.css` (600px block after the float rules) |

Markup note for 4–6: Salient has no theme.json, so WordPress renders an aligned image as
`div.wp-block-image > figure.alignright` (the wrapper has no height; the figure floats). The editor wraps it as
`div[data-align] > figure.wp-block-image`. The rules target those forms.

Gap left by design: a heading pushed below an image (text beside it shorter than the image) gets the image's
1.6em bottom margin as its space above, not the heading's own 2em / 1.6em (CSS clearance absorbs the margin).

## D36: images

| # | Change | Files |
|---|---|---|
| 7 | **Click to enlarge by default** (core lightbox, "Förstora vid klick") for every guide image and gallery image without its own setting. An editor's choice wins (choosing a link or switching it off); images linked to a file/page never get it (core only adds it to unlinked images). Same default shown in the editor's link menu. Media & Text has no lightbox in core; not added. The enlarged view shows no caption (as Salient's lightbox on the rest of the site, "Display caption in lightbox: off"). | `class-guide-frontend.php` (`image_lightbox_default`, filter `render_block_data`), `class-guide-editor.php` (`editor_settings`, filter `block_editor_settings_all`) |
| 8 | **5px corners** on all guide images, as on the rest of the site (Salient image "Border radius 5px"). Core's "Rundade hörn" (is-style-rounded) keeps its full rounding. Media & Text unchanged (its card already clips the image to 4px corners). | `guides.css`, `guides-editor.css` |
| 9 | **New image style "Med skugga"** (`is-style-fbhi-shadow`): Salient's image "Small depth" shadow, `rgba(0,0,0,.04) 0 1px 0, rgba(0,0,0,.05) 0 2px 7px, rgba(0,0,0,.06) 0 12px 22px`. Dropped in print. | `class-guide-editor.php` (`register_block_styles`), `guides.css`, `guides-editor.css`, `guides-print.css`, translation via `docs/guides/tools/make-translations.py` → `languages/sv_SE.po/.mo` |
| 10 | **Captions**: one style everywhere, grey (#4a4a4a), 14px, left-aligned, below the image. Gallery captions included (before: core's white centred text on a dark gradient over the photo). No per-caption colour/alignment controls. | `guides.css`, `guides-editor.css` |
| 11 | **Galleries with image captions** are laid out as a grid with subgrid rows (image row + caption row per figure), so images stay level and captions start at the same height. Columns: 2 on phones; from 600px the gallery's column setting (default = up to 3). A lone image in the last row fills the row (phones, default/3 and 2 columns), as in core. Galleries without image captions keep core's layout. The editor shows captions below but does not mirror the grid. | `guides.css` ("A caption below the image would make…") |

## Reverting

- One rule: delete it (each sits under its own comment in the files above) and deploy.
- Lightbox default: remove the two `add_filter` lines (`render_block_data` in `Guide_Frontend::register`,
  `block_editor_settings_all` in `Guide_Editor::register`). Images where an editor picked "Förstora vid klick"
  explicitly keep it (stored in the block).
- "Med skugga": removing the style leaves the class on images that use it; drop the CSS too, or keep the CSS.
- Everything at once: `git revert` of the commit(s) for D35/D36.

## Test section on dev

Dev only, post 13939 (Avsnitt 5), right after the heading demo: from the paragraph with class
`fbhi-spacing-demo` ("Tillfällig testyta (2026-10-12)") up to and including the next separator. Tests 1–9 cover
every row above. Daniel wants it kept on dev; **never on prod**, and editors are not pointed to it.

## Verified (2026-10-12, Chrome on dev)

Desktop 1400px and phone 390px on the frontend, desktop in the editor: all rows above measured as described;
lightbox opens on click (white background, no caption); linked image gets no lightbox. Not checked: print.
