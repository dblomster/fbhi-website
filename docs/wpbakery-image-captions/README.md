# Image captions in WPBakery ("vanliga webben") — demo on dev

Annika's request (2026-10-26, parked until 2026-11-17): captions on the WPBakery part of the site as nice as the
guide captions. Today she adds her own text block under each image, which "inte blir lika elegant och anpassat".
She liked the guide caption style (grey, left-aligned, below the image; [guides/spacing-and-images.md](../guides/spacing-and-images.md) row 10).

**Status (2026-11-18): demo on dev only; reply to Annika drafted in `incoming/2026-11-18-svar-annika-bildtexter.md`
(Daniel edits and sends, then mark it sent here).** Nothing on prod. May still be changed or reverted before prod.

## Demo pages (dev, private)

| Page | ID | URL |
|---|---|---|
| Swedish original | 13972 | https://fbhi.devcx.com/sv/demo-bildtexter/ |
| English translation (made by hand in WPBakery, linked in WPML) | 13974 | https://fbhi.devcx.com/demo-image-captions/ |

Each case shows **today** (left: image + separate text block, copied from real pages) next to the **proposal**
(right: same image with the new field): 1 simple (Lund), 2 italic + rounded corners (Bjuv), 3 small text with own
padding + enlarge on click (Goodes Prize), 4 centred image at 75 %, 5 two lines + link, 6 Media Library caption,
7 three images in a row.

## What today looks like (inventory, dev = copy of prod content)

- Salient's **Image With Animation** has no visible caption. Its "Display Caption in Lightbox" shows the Media
  Library caption only in the enlarged view. WPBakery's own Single Image (`vc_single_image`) is removed by Salient
  (`salient-core/includes/nectar-addons.php`, filter `salient_register_core_wpbakery_els` re-enables it) and is
  used nowhere.
- About 30 real captions on about 10 published items, all "image + short text block": Lund 11066 / kommuner 13887,
  Träna hjärna 11083 / 11131, Bjuv 11257, Goodes Prize 11699 / 11736, Home 6998 / 9798, review 12586 / 12603.
  Styled differently per page: black 16px body text, `<em>`, or `<font size="-1">` plus per-block padding.
- **Prod check 2026-11-18** (read-only, Daniel's OK): **40 real captions on 17 published items**, more than dev
  because Annika keeps adding them: new kommuner pages Bjuv 14566, Lund 14601, Bollnäs 16318 (2026-10-28),
  Kramfors 16328 (2026-11-16), Malmö 16352 (2026-11-17), plus MET-FINGER 14084 / 14709. Her current recipe: image
  with 5px corners + lightbox, then a text block with the whole caption in `<em>`, often with a WPBakery design
  option `border-radius: 5px` on the block (no visible effect) or padding/border settings (MET-FINGER).
  Contents: **quotes in 2** (Bjuv 14566: `"Dansa hela livet" under ledning av Tobias Karlsson (Klicka för större
  bild)` and `"Din väg till en hjärnsmart vardag!"` + a second line), **links in 0**, brackets 0, several lines in
  3 (doctoral defence ×2, Bjuv 14566), `<em>` around the whole caption in 26, `<font size="-1">` in 12, a
  `<strong>` label in 1 (Lund "Projektteamet:"). Longest ~250 characters. Not captions (same pattern, left out):
  8984, 12235, 12586 / 12603, 12748 / 12764 (and SINGER on dev).
  Encoding: the two quoted captions would show as ``` ``Dansa hela livet`` ``` in WPML's Translation Editor (the
  page itself shows normal quotes). Kommuner pages are Swedish only today, so no translator sees it. Link support
  kept in the field (Daniel, 2026-11-18) in case Annika needs it.
- Media Library captions: 42 of the 173 images used in Image With Animation have one, almost all stock-photo
  descriptions in English (also on the sv copy). So a live library caption must never be on by default.

## The proposal (what the demo code does)

Two new settings at the bottom of the **General** tab of Image With Animation:

- **Caption** (textarea): printed below the image. Line breaks kept; `a`, `em`, `strong`, `b`, `i`, `br` allowed.
  Shown in the backend editor box as "Caption: …" (admin label).
- **Use Media Library caption** (checkbox, off by default): when the Caption field is empty, shows the image's Media
  Library caption, in the page language (WPML Media copy of the attachment, same lookup as Salient's template).
  The core image block instead *copies* the library caption once when the image is picked; WPBakery has no hook for
  that, so this is a live read, like WPBakery's own Single Image.

Output: Salient's `.inner` box becomes a `<figure>` with a `<figcaption class="fbhi-image-caption">`, so the
caption follows the image's max width and alignment. If Salient's markup ever changes (e.g. the Salient 18.x
update), the caption is appended under the element as `p.fbhi-image-caption--fallback` instead: still visible,
never broken. Style = guide captions: #4a4a4a, 14px, left, `.5em` (7px) under the image, no bottom margin.

**Compared with the guide captions on prod 2026-11-18** (protected guide page 15377 unlocked as a visitor,
headless Chrome, 1400px and 390px): guide captions are Montserrat 400, 14px, #4a4a4a, line height 22.4px (1.6),
left, 7px under the image, no bottom margin; prod serves the same `guides.css` as git (same SHA-1). The demo had
line height 1.5; changed to 1.6, after which every measured property matches at both widths.

Measured on dev 2026-11-18 (headless Chrome): every caption 7px under its image at 1400px and 390px, grey 14px left;
centred 75 % image: caption 448px wide starting at the image's left edge; enlarge on click (Fancybox) still works on
captioned images; no fallbacks used. Today's text blocks: black 16px, 15px under the image.

## WPML

- **By hand** (how this site translates: no WPML translation jobs on the sv/en pairs): the field shows on the
  translation too (the demo includes the page's whole WPML translation group), and captions are just text in the
  translated page. Checked on 13974: English captions render; checkbox case shows the English library caption.
- **Translation Editor (ATE)**: `wpml-config.xml` registers `fbhi_caption` as a translatable `image_with_animation`
  attribute. Checked: after WPML re-read the config, its page-builder integration extracts every caption as a
  string (package "Page Builder Page 13972"). Caveat: WPBakery's attribute encoding is visible to the translator,
  ``` `` ``` for `"` and `` `{` `` / `` `}` `` for `[` `]` (e.g. ``` <a href=``https://…``> ```), and must be kept.
  Not sent to ATE itself (no job created).
- WPML only re-reads `wpml-config.xml` on some admin screens. On dev it was triggered by hand with
  `WPML_Config::load_config_run()` (execute-php). Do the same (or check `icl_st_settings` contains `fbhi_caption`)
  after a prod deploy.

## Files (all demo-only, gated to the demo page group)

| File | What |
|---|---|
| `salient-child/includes/image-captions-demo.php` | Field registration (`vc_add_param` on `vc_after_init`), output (`do_shortcode_tag`), CSS enqueue; constant `FBHI_IMAGE_CAPTIONS_DEMO_PAGE` = 13972 |
| `salient-child/css/image-captions-demo.css` | Caption style, loaded on the demo pages only |
| `salient-child/functions.php` | One `require_once` line (marked DEMO) |
| `salient-child/wpml-config.xml` | `image_with_animation` / `fbhi_caption` entry (inert elsewhere: only demo pages can carry the attribute) |

Gate: the field only exists when editing a demo page (`post`, `post_id` or `vc_post_id` in the request: backend
editor, its AJAX edit form, frontend editor); captions and CSS only on the demo pages. Checked: not on 11066.
Not tested: WPBakery's frontend editor.

## Dev-only data changes (to undo when the demo is removed)

- Pages 13972 and 13974 (delete).
- Media Library captions set on the "Konstaktivitet" image: 11164 (sv) "Konstaktivitet i Hjärnkåren, Lund
  (bildtext från mediebiblioteket)", 11165 (en) "Art activity at the Brain Corps, Lund (caption from the Media
  Library)". Both were empty before.
- WPML config re-read (harmless).

## Revert

Delete the `require_once` line in `functions.php`, the two files above and the `<shortcodes>` block in
`wpml-config.xml`; deploy to dev; undo the data changes above. Saved `fbhi_caption` attributes on other pages (none
yet) would simply be ignored.

## If adopted (site-wide)

1. Drop the demo gate (`fbhi_image_captions_demo_active()`), rename the file/handles without "demo".
2. Move the CSS into `css/custom.css` group 2 (custom features), per CLAUDE.md.
3. Deploy dev → check → prod; re-read WPML config on prod; purge Nginx cache.
4. Existing captions keep working as text blocks; Annika moves them into the field when she likes (or a one-off
   script for the ~30 cases, on prod with Daniel's OK per step).
5. Add "captions still inside the image figure (no fallback)" to the Salient update test list (we are on 15.0.9;
   latest 18.4.4, 2026-10-28).
