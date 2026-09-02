# FBHI Guides — decision log and open questions

Decisions were taken in conversation with Daniel on 2026-09-02 unless noted. Newest at the bottom of
each section. When an open question gets answered, move it to "Decided" and update plan.md.

## Decided

| # | Topic | Decision | Why |
|---|---|---|---|
| D1 | Environment | Build and test on **dev (fbhi.devcx.com)**. Prod MCP is disabled for this project; prod deploy only on explicit go. | Safety; no staging between MCP and prod. |
| D2 | Deploy | Existing `./upload.sh` with target `d` (dev) / `p` (prod). Nothing to add. | Script already had both targets. |
| D3 | Test content | Load the FINGER guide onto dev via Novamira dev MCP together with Daniel, each write confirmed. Daniel supplies draft content. | Real content exposes real layout needs. |
| D4 | URL base | `guide` for now, held in one constant (`FBHI_GUIDE_SLUG`) so it can be renamed later. | "Prepared to change it if needed." |
| D5 | Listing page | No guide archive/listing page. Maybe late in the project if time allows. | Only one guide (FINGER), Swedish only. |
| D6 | Introduction | Root post content **is** the introduction, as on the reference site. Unnumbered chapters (e.g. "Om handboken") are just chapters without a numeric label. Final shape confirmed against draft content. | Reference site works this way. |
| D7 | Chapter designation | **Free-text label** meta per chapter (e.g. "Steg 1"), not auto-numbering. | Daniel's call; draft content will show the mix. |
| D8 | Last updated | Use the page's own `post_modified` date. | Default while Q2 is open. |
| D9 | Accent colour | Set once per chapter (meta); index page, chapter header and nav accents pick it up automatically; deeper children inherit. Picker offers the FBHI underpage palette as swatches. | Spec + Daniel: "a colour you set on each underpage which the index page uses automatically." |
| D10 | Images | No image in chapter header or index cards. Images/video only inside content, via core blocks. | "No image in header afaik." |
| D11 | ACF hero header | Skip the ACF hero on guide pages. Guides must still blend with the overall site design (Salient header/footer/typography/containers). | Daniel: "skip it, but the guides should melt in." |
| D12 | Sidebar model | Copy the reference: one sticky sidebar = chapter list with current chapter expanded to show its H2 TOC; mobile = single dropdown above the chapter header. "We try that first." | Covers chapter nav + TOC in one component. |
| D13 | Breadcrumbs | None. | "We can navigate without breadcrumbs." |
| D14 | Print | Print button (current page only) + dedicated print CSS + an editor-insertable **print page break** (block style on core Separator, invisible on screen). | Daniel wants control over page breaks in print. |
| D15 | Components | Model on the reference site: info box, callout, checklist box, interview section. Built as core-block styles + patterns; images/video via native blocks. | Spec preference order: core → styles → patterns → custom block. |
| D16 | Block tooling | **No build pipeline.** Dynamic (server-rendered) block with block.json + hand-written editor JS using `wp.element.createElement`. | Repo has no Node; deploy is rsync. |
| D17 | Index placement | Auto-append cards after root content; optional `fbhi/guide-index` block overrides placement. | Reference renders the index automatically after the intro. |
| D18 | Menu highlighting | Guide pages highlight the root guide's own menu entry (Daniel adds it to the Swedish menu). | One guide, Swedish only. |
| D19 | Docs | `docs/guides/` with README (quick answers), plan, decisions, reference-site notes, original brief; pointer in CLAUDE.md. Structured so Claude can answer Daniel's questions from it. | Daniel asks Claude rather than reading md. |
| D21 | Excerpt (was Q1) | One native excerpt serves both the index card and the chapter-header intro. | The drafts each open with exactly one summary paragraph; the reference site does the same. (2026-09-02, from draft) |
| D22 | Card numeral (was Q3) | Cards show the free-text label ("Avsnitt 1") + title + excerpt; no automatic number circle. | Chapters are "Avsnitt", numbering is editorial. Can be revisited. |
| D23 | Palette (was Q4) | Accent picker offers the four FBHI accent colours (teal, green, yellow, peach); backgrounds use the manual's tint of each; text stays navy/black. Values in content-notes.md. | Graphic manual received 2026-09-02. |
| D24 | TOC depth (was Q10) | H2 in the TOC, with nested H3 available behind a switch (off by default). | Draft chapters are long and use H3 legitimately. |
| D25 | Fifth chapter colour (was Q11) | Reuse the four accents; the colour must stay easy to change per chapter from the editor panel. | Daniel 2026-09-02. |
| D26 | Draft sections (was Q12) | Load the draft as-is on dev, no draft flags. Dev is for creating the guide and gathering feedback. | Daniel 2026-09-02. |
| D27 | Verification | Every phase is checked in Chrome (MCP) on dev in both desktop and mobile emulation before reporting. | Daniel 2026-09-02. |
| D28 | Translation tooling | .po is hand-maintained; .mo and editor JED .json are generated with a Python one-off (no msgfmt/wp-cli locally). To add strings: edit the table in the generator, regenerate, deploy. Generator to be kept at `docs/guides/tools/make-translations.py`. | 2026-09-02, practical. |
| D29 | Content import route | Chapter HTML is staged in the theme, then pushed with the editor's own REST session from Chrome (`wp.apiFetch`), then the staging files are removed. Avoids pasting 100 KB through MCP calls and avoids execute-php. | 2026-09-02. |
| D20 | Guide CSS location | Module-local `assets/guides/*.css`, not `css/custom.css`. Still git-tracked and deployed; CLAUDE.md notes the exception. | Conditional loading + print sheet; keeps the module extractable. |

## Open questions (with current default)

| # | Question | Default until decided | Waiting on |
|---|---|---|---|
| Q2 | Root index page "last updated": its own date, or the newest date across the guide + chapters? Show time as the reference does? | Own date, date only. | Daniel |
| Q5 | Bottom navigation style: Salient-style prev/next bar, or the reference's large "next chapter" teaser band? | Salient-style bar, with next-chapter title. | Design round on dev |
| Q6 | Chapter header band: full browser width or content-column width? | Content-column width (reference). | Design round on dev |
| Q7 | Does the root guide page get its own accent (tinted intro band as on the reference)? | Yes, optional, same meta. | Design round on dev |
| Q8 | Which video sources to allow: core/video (upload) and core/embed YouTube + Vimeo? | Both. | Daniel |
| Q9 | Guide listing page later? | Not in scope. | Time |
