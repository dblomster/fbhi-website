# Guide page subtitles (2026-10-26, D38)

Annika's request (2026-10-26): the chapter titles have become long ("Hur kommer man igång? – Skapa rätt
förutsättningar"). The second part should be a **subtitle** on the chapter page and on the chapter's card on the
guide start page, but not in the menu. She found no way to add such a field herself.

**Status: on dev and prod since 2026-10-26** (commit d1e4475). Prod titles are unchanged; splitting them (shorter
title + subtitle field on 15376–15378) is Annika's content step. Dev test values: subtitles on 13902–13904.

## Behaviour

- New field **Underrubrik** in the editor's "Guidesida" panel, between Etikett and Text på utskriftsknappen.
  Plain text, per page, no inheritance. Empty = nothing shown (pages without it look exactly as before).
- Shown **under the H1 in the chapter header band** (the root page too, if set) and **under the card title** on
  the start page's index cards.
- **Not** shown in the sidebar menu, the mobile menu summary, prev/next, the browser tab / `<title>`, or the
  "Läs mer: …" screen-reader name of the card button. Those keep the (now shorter) title only.
- Printed: yes, under the title (15pt) and on the cards (11pt).
- **Editor**: the subtitle appears live under the title in the canvas's tinted title band while typing in the
  panel (read-only there; hovering says "Ändras i panelen Guidesida."). The start page's index-block preview shows
  the cards' subtitles once the chapters are saved (server-rendered).
- **Editor: intro (excerpt) too** (Daniel, same day): the manual excerpt ("Redigera utdrag" in the sidebar) is
  shown under the title/subtitle in the editor's band, as the frontend header's intro: 18px, paragraphs on blank
  lines, single line breaks kept, 12px under a subtitle / 18px under the title. Read-only, live while editing;
  hovering says "Ändras via ”Redigera utdrag” i sidopanelen.". Text only (HTML in an excerpt shows as its text).
  Not shown when there is no manual excerpt, like the frontend. The editor always shows the real excerpt, also on
  password-protected pages (the frontend shows core's protected text while locked).
- **WPML**: `action="translate"` like the label and print-button text.
- **Password-protected pages**: the subtitle shows while locked, like the title (core shows titles of protected
  posts). It is part of the title, not of the protected content.
- Markup: `<p class="fbhi-guide-header__subtitle">` / `<p class="fbhi-guide-card__subtitle">` after the heading
  (a plain paragraph, not a heading, so it does not enter the document outline or the TOC).

## Sizes

Black like the content H3 and the same size as an H3 (22 / 20px), one step lighter (600). Spacing follows the
index cards: title → subtitle → intro look evenly spaced. Measured from the last baseline to the next line's cap
height: cards 22 / 24px, header 25 / 26px desktop and 23 / 24px phone. To get there the header intro moves up
when a subtitle is set (12px instead of 18px). Decided by Daniel 2026-10-26 after comparing on dev.
Earlier versions the same day (replaced): navy 24px / 500, 10px under the title, intro 18px; then black 600 with
4px / 18px (subtitle too close to the title, intro too far).

| Where | Title | Subtitle |
|---|---|---|
| Chapter header, desktop | 38px / 700 navy | 22px / 600 black, line height 1.35, 10px under the title; intro 12px below (18px without a subtitle) |
| Chapter header, ≤ 999px | 28px | 20px, same gaps |
| Index card | 22px / 700 navy | 18px / 600 black, 4px under; intro 12px below (10px on cards without a subtitle) |
| Print header / card | 24pt | 15pt / 11pt, black, 600; 5pt under the title |
| Editor title band | 38px | 22px / 600 black, 10px under |

## Change log

| # | Change | Files |
|---|---|---|
| 1 | Meta `_fbhi_guide_subtitle` (string, REST, `sanitize_text_field`), getter `Guide_Meta::subtitle()` | `includes/guides/class-guide-meta.php` |
| 2 | WPML: translate the field | `wpml-config.xml` |
| 3 | Header: subtitle after the H1; grid area `subtitle` (desktop next to the print icon's column, ≤999px full width) | `templates/parts/chapter-header.php`, `assets/guides/guides.css` ("Subtitle", header grid, ≤999px block) |
| 4 | Index card: subtitle after the card title | `templates/parts/index-cards.php`, `guides.css` (`.fbhi-guide-card__subtitle`) |
| 5 | Print sizes | `assets/guides/guides-print.css` |
| 6 | Panel field + live copy under the title in the editor canvas | `assets/guides/guides-editor.js` (`syncSubtitle`), `includes/guides/class-guide-editor.php` (`metaSubtitle` in the config), `assets/guides/guides-editor.css` ("Subtitle") |
| 7 | Swedish strings: Underrubrik, help text, meta description, "Ändras i panelen Guidesida.", "Ändras via ”Redigera utdrag” i sidopanelen." | `docs/guides/tools/make-translations.py` → `languages/sv_SE.po/.mo`, editor JSON |
| 8 | Editor: the excerpt as the header intro, in one read-only block (`.fbhi-guide-editor-band`) with the subtitle | `guides-editor.js` (`syncHeaderBand`, `excerptParagraphs`), `guides-editor.css` ("Subtitle and intro") |

## Reverting or adjusting

- **Sizes/weights/colour**: the rules listed under "Sizes" above, in `guides.css` (+ `guides-editor.css` for the editor,
  `guides-print.css` for paper). Change them in both the web and the editor file.
- **Don't show on the cards**: remove the `fbhi_card_sub` lines in `index-cards.php`.
- **Show in the menu as well**: add `Guide_Meta::subtitle()` to `sidebar-nav.php` (not done by design).
- **Remove the feature entirely**: revert the commit. Saved subtitles stay in the post meta, unused and invisible;
  the titles would then need their second part back by hand.
