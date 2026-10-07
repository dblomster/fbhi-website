# Guide heading scale (2026-10-07, D34)

Heading sizes in the guide content (`.fbhi-guide__content`), the editor canvas and print.
Decided with Daniel 2026-10-07 after measuring dev in Chrome. Deployed to dev and prod the same day.

## Where the values live

- **Web + editor:** CSS variables in `salient-child/assets/guides/guides.css`, desktop values on `:root` at
  the top, phone/tablet (< 1000px) values on `.fbhi-guide__content` in the `@media (max-width: 999px)`
  block. `guides-editor.css` reads the same variables (guides.css is loaded in the editor canvas too), so
  changing a value changes the editor as well. The editor only uses the desktop values.
- **Print:** the same variables redefined in pt in `guides-print.css` (section "Heading sizes").
- Variables: `--fbhi-guide-h2-size` … `--fbhi-guide-h6-size`, `--fbhi-guide-box-heading-size` (H3 in
  info / callout / checklist boxes and in Media & Text).

## Current values (after)

| | Desktop | Phone (< 1000px) | Print | Line height | Colour |
|---|---|---|---|---|---|
| Page title (H1, header band) | 38px | 28px | 24pt | 1.15 | navy (black in print) |
| H2 | 28px | 24px | 18pt | 1.2 | teal (black in print) |
| H3 | 22px | 20px | 14pt | 1.3 | black |
| Box / Media & Text heading (H3) | 20px | 19px | 13pt | 1.3 | navy |
| H4 | 19px | 18px | 12pt | 1.35 | black |
| H5 | 17px | 16px | 11pt | 1.4 | black |
| H6 | 15px | 14px | 10pt | 1.4 | black |
| Body text | 17px | 16px | 11pt | 1.6 (1.45 print) | black |

All headings weight 700, Montserrat, no letter-spacing or uppercase. Space above top-level headings:
H2 2em, H3 1.6em, H4 1.4em, H5/H6 the normal 1.2em; space below any heading 0.6em. Print: 18 / 14 / 12pt above.

Sizes apply to headings **at any depth** (inside groups, boxes, columns), not only top-level ones; the
index cards' own headings (`.fbhi-guide-*` classes) are excluded. H1 inside the content is deliberately
left unstyled (Daniel: keep it selectable, Salient's style).

## Values before this change (for reverting)

| | Desktop | Phone (< 1000px) | Print | Source |
|---|---|---|---|---|
| Page title | 38px | 28px | 22pt | guides.css / guides-print.css |
| H2 (top level) | 28px / 1.2, teal | 24px | 16pt | guides.css |
| H3 (top level) | 21px / 1.3, black | 21px (did not shrink) | 13pt | guides.css |
| H3 in boxes / Media & Text | 19px, navy, **line height 37px** (Salient) | 19px | not set (screen px) | guides.css + Salient |
| H4 | 24px / 28px, weight 600 | 19.2px | — | Salient theme options |
| H5 | 20px / 29px, weight 500 | 20px | — | Salient theme options |
| H6 | 16px / 20px, weight 400 | 16px | — | Salient theme options |
| H2 inside a group/box | 52px / 52px, weight 900 | 26px | — | Salient theme options |
| Body | 17px / 1.6 | 16px | 11pt | guides.css |

Salient's site-wide heading settings (theme options, unchanged, still used everywhere outside the guide):
H1 42/42 400 uppercase, H2 52/52 900, H3 32/37 600, H4 24/28 600, H5 20/29 500, H6 16/20 400, body 16/20;
phone factors H1 60 %, H2 50 %, H3 70 %, H4 80 %, H5/H6 100 %.

Git: the commit "Guides: heading scale H2–H6 …" holds the change; reverting that commit restores the
values above (including the two spacing bugs below).

## Why these values

- Body 17 → H2 28 is fixed (Daniel happy with H2). The even ("modular") midpoint between them is
  √(17 × 28) ≈ 22, giving steps of 1.29 and 1.27, the usual range (1.2–1.33) for reading-heavy pages.
  H3 is the most-used level (~30 per chapter vs 5–10 H2) and what readers scan within a page; H2 also
  differs by colour, so H3 can sit fairly close to it. Matches the index-card titles (22px).
- H4 = midpoint between body and H3 (√(17 × 22) ≈ 19). H5 = body size bold, H6 a bit smaller: common
  practice for the deepest levels (GOV.UK, Bootstrap) — they are rare in running text.
- Box heading 20/19 so an H4 inside a box stays below the box's own heading (navy also separates them).
- Print keeps the same ratios to body text (11pt).

## Spacing bugs fixed in the same change

1. Editor: `> h2.wp-block { margin-top: 2em }` lost to `> .wp-block + .wp-block` on specificity, so
   headings in the editor always had only 1.2em above them. Now `> .wp-block + h2.wp-block` etc.
2. Boxes (frontend and editor): Salient has no theme.json, so core wraps group content in
   `.wp-block-group__inner-container` and the box rules (`> :first-child`, `> * + *`) never matched. A
   second heading in a box sat directly under the paragraph above it; callout/checklist boxes had ~10px
   extra at the bottom (list margin). Rules now match both forms; the gap under a box heading stays 0.6em.
3. A heading directly after an image/file/Media & Text block keeps its own space above (2em / 1.6em),
   not the 1.8em figure gap.

## Demo on dev

Chapter "Innehåll, övningar och tillämpning" (Avsnitt 5, post 13939, dev only) starts with a temporary
demo: H2–H6 with lorem text, a wrapping H3 and H4, and a fact box with a wrapping H3 and an H4, followed
by a separator. Daniel wants it kept on dev; **demo content never goes to prod**. To remove it, delete
everything from the paragraph with class `fbhi-heading-demo` up to and including the first separator
(revision before the demo: the one preceding 2026-10-07 in the post's revisions).
