# FBHI Guides (digital handbook) — project docs

Custom guide/handbook system for fbhi.se, modelled on Jämställdhetsmyndigheten's
"Inget att vänta på" digital handbook. First guide: **FINGER Implementation
Guide**, Swedish only, built and tested on **dev (fbhi.devcx.com)** first.

**Status (2026-09-02): planning done, colour palette and draft content received and analysed,
green light given for phase 1 (post type + meta + editor).**

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
