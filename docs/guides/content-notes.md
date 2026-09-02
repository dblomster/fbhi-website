# FINGER guide — draft content and colour palette (as received 2026-09-02)

Source files: `incoming/guide draft content/` (git-ignored): four `.docx` chapters + `Color palette.pdf`.
Text extracted to the session scratchpad; re-extract with `unzip -p file.docx word/document.xml` if needed.

## Colour palette (FBHI graphic manual, exact RGB from the PDF vector fills)

| Role | Name | Hex | Tint (for backgrounds) | Notes |
|---|---|---|---|---|
| Primary | Navy | `#213A6C` | — | CMYK 99/83/28/15; brand colour |
| Primary | Light grey | `#D5D3D3` | — | CMYK 35/27/28/6 |
| Primary | Black / White | `#000000` / `#FFFFFF` | — | |
| Accent | Teal | `#006885` | `#BECCD8` | RGB listed as 0/104/133 |
| Accent | Green | `#90BB94` | `#D6E4D5` | RGB listed as 142/187/149 (`#8EBB95`) — use PDF fill `#90BB94` or the listed value; visually identical |
| Accent | Yellow | `#FFE6AC` | `#FFF3D8` | RGB listed 254/230/172 |
| Accent | Peach | `#E5A883` | `#F5DDCD` | RGB listed 227/168/131 (`#E3A883`) |

The manual says accent colours "can be toned down when used as backgrounds" — the tint column is the
manual's own lighter swatch. Plan: chapter accent = one of the four accents; card/header background =
its tint; text stays dark (navy/black) on the tints, so contrast is never an issue. Teal is the only
accent dark enough for white text; treat the accent as a *background/decoration* colour, not a text colour.
Four accents = four current chapters; a fifth chapter will need to reuse a colour or add one (ask Daniel).

## Draft structure

Chapters are called **"Avsnitt 1–4"** (not "Steg"). Each document has the same shape:

1. Title, e.g. "Avsnitt 2 - Nuläge, förarbete och att skapa förutsättningar"
2. One summary paragraph ("Detta avsnitt beskriver …") — **this is the excerpt**: it works unchanged as
   card description and chapter-header intro (resolves Q1: one shared text, as on the reference site).
3. A manual "Innehållsförteckning" bullet list — **drop when loading**; the TOC is generated.
4. Numbered H2 sections (Word style Rubrik2), bullet lists (Liststycke), and many *unstyled bold
   paragraphs* used as sub-headings ("Börja med att formulera en enkel målbild", "Fundera på:") — these
   should become **H3** in WordPress. Chapter 3 already uses real H3 (Rubrik3) under 4.1–4.3 and under
   "Vanliga utmaningar".

| Chapter | Words | H2 sections | Notes |
|---|---|---|---|
| Avsnitt 1 – Bakgrund och varför FINGER spelar roll | ~900 | Förord · Att använda guiden · Bakgrund och introduktion · FINGER i praktiken · Varför FINGER spelar roll | "Förord" is a brief for Miia to write; "Bakgrund" is a purpose note, not final text. Comment asks for references. |
| Avsnitt 2 – Nuläge, förarbete och att skapa förutsättningar | ~2900 | 11 sections (numbering out of order: 1,2,3,4,5,7,9,6,8,11) | Comments: mention the upcoming film; link the Kalmar workshop PPT (→ File block). Placeholder links "finns i här", "kapitlet om xxx". |
| Avsnitt 3 – Att utforma och genomföra FINGER-aktiviteter | ~3700 | Från kunskap till förändring · Olika former · Längd/intensitet · Mer omfattande program (H3 4.1–4.3) · Vanliga utmaningar (H3 ×4) | Longest chapter; "webinariet XX" placeholder. Ends by announcing a coming chapter on cognitive impairment → Avsnitt 5+ expected. |
| Avsnitt 4 – Planering av aktiviteter och viktiga överväganden | ~3150 | 8 sections (some numbered 5–8, some not) | Placeholder "finns i här X", "FBHI:s webbplats xx". |

Total ≈ 10 700 words. No images, tables, boxes or hyperlinks in the drafts; only headings, paragraphs
and bullet lists. Editors will add images/video/files later in Gutenberg.

## What this means for the build (fed back into plan.md / decisions.md)

- **Labels**: free text "Avsnitt 1" … (D7 confirmed). Section numbering inside chapters is editorial
  and inconsistent — leave it to the editors, do not auto-number.
- **Excerpt**: one field for card + header (Q1 → decided).
- **Card numeral**: no automatic big number; show the label (Q3 → decided). A number circle can be
  reconsidered if the editors want it.
- **TOC depth**: chapters are long (up to 3 700 words) and use H3 legitimately → build the TOC for H2
  with **optional nested H3**, off by default, switchable per site (Q10 → plan updated).
- **Components actually needed now**: "Fundera på:" question lists → *Callout* style; "Tänk på att
  använda:" tip lists → *Info box*; chapter-end checklists do not exist yet but Avsnitt 2 §4/§6 read like
  them → *Checklist* style stays. No interview sections in the draft → the *Interview* pattern is
  deferred until content needs it.
- **File block** needed (Kalmar PPT), **Embed/Video** for the film.
- **Migration**: load via Novamira (Gutenberg write ability) from cleaned Markdown/HTML per chapter:
  strip manual TOC, promote bold paragraphs to H3, keep lists. Placeholders ("XX", "xx", "här X") left
  as-is for Miia/editors, but listed in a review note on dev.
