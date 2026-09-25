# FBHI Guides (digital handbook) — project docs

Custom guide/handbook system for fbhi.se, modelled on Jämställdhetsmyndigheten's
"Inget att vänta på" digital handbook. First guide: **FINGER Implementation
Guide**, Swedish only, built and tested on **dev (fbhi.devcx.com)** first.

**Status (2026-09-02): planning done, colour palette and draft content received and analysed,
phases 1–7 deployed to dev 2026-09-02: post type, meta, editor panel, block curation, guide template with
sidebar/TOC/nav, guide-index block, components (block styles + patterns), print CSS, Swedish translations, all four
chapters loaded, first design-review round done (borders dropped, nav/bottom-nav tweaks, mobile fixes).
Daniel added "FINGER-guiden" to the Swedish menu on dev; highlighting works. Prod deploy only on explicit go.**

**Update 2026-09-08**: chapter 4's "Uppföljning & utvärdering" section replaced with Patrik's 2026-09-03 draft
(two typos fixed: "ochofta", missing full stop; "xx"/"tex" left as written). Eight `exempel-handbok-*` images
placed as plain core/image blocks (content width, size "large", some with captions) across all four chapters and
one example core/table in chapter 2 ("Att välja nivå") so the editors can see how images/tables look — all
placeholders, to be replaced by hand. Print button now reads "Skriv ut avsnittet"; index-card links no longer
print their URL; lists sit a little closer to the paragraph before them (.7em instead of 1.2em). Later the same day: figures (images/tables) had lost their
top margin to `figure { margin: 0 }` — fixed, and figures/file boxes now get 1.8em air above and below. Two example
PDF links added in chapter 2 ("Skapa trygghet i arbetet"): an inline text link and a core/file block (attachments
13933/13935, sv). Links ending in .pdf get a small document icon after the text; the file block is styled as a grey box
with a pill "Ladda ner" button. Note for hand-written core/file markup: the download button needs
`aria-describedby` matching the file link's `id`, otherwise the editor reports invalid content.
Also new: a third field in the "Guidesida" panel, **Text på utskriftsknappen** (`_fbhi_guide_print_label`, WPML
action=translate, default "Skriv ut" when empty). On dev the root says "Skriv ut sidan" and the four chapters
"Skriv ut avsnittet".
Print fix (same day): PDF links (inline and file block) print the document icon as an inline SVG image followed by
the URL — the screen icon is a masked background, which browsers drop on paper and which outranked the URL rule;
the file block's download button is now hidden in print. URLs after external links in print are a deliberate
convention (paper has no clickable links) and stay.

**Update 2026-09-15** (during the 2026-09 update round, WP 7.1 on dev):
- **Editor CSS split**: `guides-editor.css` is now canvas-only (loaded inside the editor iframe via
  `enqueue_block_assets`); the sidebar-panel rule moved to `guides-editor-ui.css` (`enqueue_block_editor_assets`).
  Removes the duplicate copy WP 7.1 was making into the iframe.
- **Editor canvas mirrors the frontend**: 780px content column, Montserrat 17px, teal H2 / black H3 at frontend
  sizes, the same block rhythm (1.2em, tighter after headings, more air around figures/files), tinted title band,
  boxes, file block, tables, quotes, Media & Text card. Values are copied from `guides.css` — keep both in sync.
- **Media & Text** (`core/media-text`) is now an allowed block. Frontend/print styling added (grey card, text
  padding, H3 in navy). Example placed on chapter 5 (13939) under "Övningar" with the Nordic-walking image (13919).
- **Unpublished pages**: visitors only ever see published pages, and a page whose ancestor (e.g. the guide root)
  is unpublished is a 404 for them even if published itself. Logged-in editors (`edit_others_pages`) see draft /
  pending / scheduled / private pages in the sidebar, index cards and prev/next with a small status badge, and a
  yellow "Förhandsvisning" notice above the chapter header explains why visitors cannot see the page. Those
  responses send no-cache headers. Purpose: copy the guide to prod as drafts and review it in place before publishing.
  Verified on dev 2026-09-15 with a temporary draft chapter and the root temporarily set to draft: visitors got 404
  on every guide URL, editors got 200 with the notices/badges and working pretty links. (Watch out when testing
  as "editor" via a Novamira admin link: the session expires after an hour and everything silently turns into
  the visitor view.)

## Outstanding (parked, revisit later)

- **Guide listing page** (all guides) — not built; only one guide exists. See Q9 in decisions.md.
- **Chapter 1 (13901)** was loaded before the converter existed: no H3 promotion / callouts yet. Re-run the
  converter (content-notes.md → "Migration") or fix by hand in the editor.
- **Draft placeholders** in the text ("XX", "kapitlet om xxx", "finns i här") — editorial, for Miia/editors.
- **Fifth chapter** exists on dev as a placeholder (13939, navy). Decide the colour: navy's tint is close to teal's, so the two cards look alike side by side (D25 said reuse an accent).
- **Open design questions** Q2, Q5–Q8 in decisions.md still carry their defaults.
- **Editor-facing docs**: a short "how to edit the guide" note for editors has not been written.
- **Image/video handling on desktop, mobile and print** (parked 2026-09-25): revisit **after Annika has added
  real content**, then decide whether changes are needed. Findings from the demo prep (not verified in the editor):
  - Core image block controls (aspect ratio, width/height, scale cover/contain, resolution, crop) are a single
    setting for every screen size, with no per-device values. **Advice to editors for now: use an aspect ratio, not a fixed
    height**, because the height then scales down with the width on mobile.
  - Fixed height with aspect ratio *Original* can **squash** images on mobile: the inline height stays, but
    `max-width: 100%` caps the width, and no `object-fit` is output in that case.
  - Print: `guides-print.css` forces `img { height: auto !important }`, so a fixed height is ignored on paper
    (an aspect ratio survives).
  - Possible fixes if needed: a guide block style such as "Limited height" with responsive + print values in
    `assets/guides/` (editors click a style, no CSS); per-device show/hide only if WP 7.1 supports it (unchecked).
  - Wide/Full alignment is not available (Salient doesn't declare `align-wide`); `core/cover` is deliberately not allowed.
  - Check video (`core/video`, `core/embed`) the same way: desktop, mobile, print.
- **Print icon near the top** (parked 2026-09-25, same review): consider an extra print icon/button higher up
  (e.g. by the chapter header) alongside the existing print link at the bottom of the page.
- **Prod (2026-09-24)**: module deployed, guide copied **as drafts** (root 15374 `/sv/guide/finger/`, chapters
  15375–15379), no menu item — visitors get 404 until publishing. Example media copied with their exact
  `exempel-handbok-*` names (en+sv WPML pair per file, 15354–15373): delete/replace them before publishing.
  Details and ID table in [../updates-2026-09-24/README.md](../updates-2026-09-24/README.md) § Guide copy log.
  From now on `./upload.sh p` is safe again (dev and prod carry the same theme).

**Dev content (fbhi.devcx.com)**: root guide "FINGER-guiden" = post 13900 (`/sv/guide/finger/`); chapters
13901 Bakgrund (Avsnitt 1, teal) · 13902 Nuläge (Avsnitt 2, green) · 13903 Utforma (Avsnitt 3, yellow) ·
13904 Planering (Avsnitt 4, peach) · 13939 Innehåll, övningar och tillämpning (Avsnitt 5, navy — **placeholder text only**, created 2026-09-08 to judge a fifth colour). Chapters 1–4 carry the draft text (13901 without H3 promotion; 13902–13904 with H3s and callouts).

## Files in this folder

| File | What it answers |
|---|---|
| [plan.md](plan.md) | *How* is it built: content model, file layout, each feature's mechanism, phases, QA list |
| [decisions.md](decisions.md) | *Why* it is built that way: every decision taken with Daniel, plus the open questions and their current defaults |
| [content-notes.md](content-notes.md) | The FINGER draft (4 chapters) and the FBHI colour palette with exact hex values, and what they imply for the build |
| [reference-site.md](reference-site.md) | What the reference handbook actually does (observed with Chrome), so we do not have to re-inspect it |
| [original-brief.md](original-brief.md) | Daniel's original implementation brief, verbatim |

## Quick answers

- **Where does guide code live?** `salient-child/includes/guides/` (classes, blocks, templates) plus
  `salient-child/single-guide.php` (thin) and `salient-child/assets/guides/` (CSS/JS). See plan.md "File layout".
- **Post type?** `guide`, hierarchical. Root post = guide index page; children = chapters/steps.
  URL base `guide` held in one constant so it can change. No archive, no listing page for now.
- **Editor?** Gutenberg only, curated core-block list, no WPBakery. Custom block only for the
  auto-generated index (`fbhi/guide-index`). Everything else is core blocks + block styles + patterns.
- **Colour?** One accent colour meta per chapter, chosen from the FBHI underpage palette; index card,
  chapter header and sidebar accents pick it up automatically; deeper children inherit from their chapter.
- **Navigation?** One sticky sidebar: chapter list with the current chapter expanded to show its H2
  headings. On mobile it collapses to a dropdown above the chapter header. Prev/next + "back to guide" at bottom.
- **Deploy?** `./upload.sh d` for dev, `./upload.sh p` for prod (prod not before Daniel says so).
- **Content on dev?** Loaded via Novamira dev MCP together with Daniel; every write confirmed first.
- **Open questions?** Bottom of decisions.md.
