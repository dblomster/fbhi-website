# FBHI Guides (digital handbook) — project docs

Custom guide/handbook system for fbhi.se, modelled on Jämställdhetsmyndigheten's
"Inget att vänta på" digital handbook. First guide: **FINGER Implementation
Guide**, Swedish only, built and tested on **dev (fbhi.devcx.com)** first.

**Status (2026-09-02): planning done, colour palette and draft content received and analysed,
phases 1–7 deployed to dev 2026-09-02: post type, meta, editor panel, block curation, guide template with
sidebar/TOC/nav, guide-index block, components (block styles + patterns), print CSS, Swedish translations, all four
chapters loaded, first design-review round done (borders dropped, nav/bottom-nav tweaks, mobile fixes).
Daniel added "FINGER-guiden" to the Swedish menu on dev; highlighting works. Prod deploy only on explicit go.**

## Outstanding (parked, revisit later)

- **Guide listing page** (all guides) — not built; only one guide exists. See Q9 in decisions.md.
- **Chapter 1 (13901)** was loaded before the converter existed: no H3 promotion / callouts yet. Re-run the
  converter (content-notes.md → "Migration") or fix by hand in the editor.
- **Draft placeholders** in the text ("XX", "kapitlet om xxx", "finns i här") — editorial, for Miia/editors.
- **Fifth chapter** (kognitiv svikt) announced in the draft — reuse a palette colour (D25).
- **Open design questions** Q2, Q5–Q8 in decisions.md still carry their defaults.
- **Editor-facing docs**: a short "how to edit the guide" note for editors has not been written.
- **Prod**: nothing deployed; wpml-config + the `guide` type will need the same WPML language handling on prod
  (posts created via MCP get no language — see D-notes in plan.md § WPML).

**Dev content (fbhi.devcx.com)**: root guide "FINGER-guiden" = post 13900 (`/sv/guide/finger/`); chapters
13901 Bakgrund (Avsnitt 1, teal) · 13902 Nuläge (Avsnitt 2, green) · 13903 Utforma (Avsnitt 3, yellow) ·
13904 Planering (Avsnitt 4, peach). All four chapters carry the draft text (13901 without H3 promotion; 13902–13904 with H3s and callouts).

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
