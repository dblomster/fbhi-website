# FAQ accordion page ("Frågor och svar om FINGER")

Docs for the accordion version of the FAQ page. The page **body** lives in
WordPress (WPBakery), so this folder keeps a tracked, diffable copy of it. The
**styles** now live in the theme at `salient-child/css/custom.css` (group 2,
"FAQ accordion") — version-controlled and deployed by `upload.sh`, not in the
Customizer.

- `shortcode.txt` — the full WPBakery shortcode for the page body.
- Styles → `salient-child/css/custom.css` (search "FAQ accordion").

## Page

- Live (accordion): https://fbhi.se/sv/fragor-och-svar-om-finger/

The accordion shipped to the live FAQ page (the old `…-test` staging page is
gone). The page carries the `faq-accordion` el_class, so the styles below apply.

## How it works

Each FAQ question is a Salient **Toggle Panel** (`[toggle]`), grouped under a
green section heading inside its own **Toggle Panels** group (`[toggles
style="minimal"]`). The original page's flat list of yellow `<h5>` question
boxes + answers was converted so every question collapses/expands; the four
green category headings are kept as-is between the groups.

## How to enable / update

1. **Page body** — edit the page in WPBakery, switch the editor to
   Classic/Text mode, and paste the contents of `shortcode.txt`.

2. **Turn on the styling** — the CSS is scoped to a `faq-accordion` class on the
   outermost row. Add it one of two ways:
   - Text editor: add `el_class="faq-accordion"` to the opening
     `[vc_row ...]` tag, **or**
   - Visual editor: outer Row → pencil → **General** → **Extra class name** →
     `faq-accordion`.

3. **Styles** — already in `salient-child/css/custom.css` (group 2, "FAQ
   accordion"). Nothing to paste; deploying the theme via `upload.sh` ships
   them. (They previously lived in the Customizer's Additional CSS and were
   migrated into the theme on 2026-06-10.)

After deploying/editing on production, purge the Nginx cache (Nginx Helper).

## Notes / knobs

- Bar look matches the classic page: `#fee6ac` fill, black 18px text, radius 3px.
- Question text colour can be switched to brand teal by changing `color: #000`
  to `color: #006885` in the `.toggle-heading` rule.
- Multiple panels can be open at once (typical for an FAQ). Single-open
  behaviour would be a Toggle-group setting, not CSS.

## CSS rule notes

The comments in `custom.css` are kept terse. The rationale lives here, rule by
rule:

- **Question bar (`.toggle-heading`)** — `font-size: 18px` sits just below the
  ~19px green section heading so the section reads as the dominant level. The
  `52px` right padding reserves room for the absolutely-positioned `+/-` icon.
- **Answer body** — the rule intentionally mirrors Salient's
  `div[data-style="minimal"] .toggle[data-inner-wrap="true"] > div .inner-toggle-wrap`
  selector, prefixed with `.faq-accordion`, so it wins on specificity. If answer
  padding ever looks wrong after a Salient update, check that selector.
- **Green section heading** — each green bar is a WPBakery text column wrapping
  an `h4`. The rule targets that `h4` (stable) rather than the per-element
  `vc_custom_*` class, which WPBakery regenerates whenever the page is edited.
  Padding `10px 14px` matches the question bars' top/bottom (10px) and aligns
  the heading text to the question text (14px left). `!important` is required
  because WPBakery emits the column's per-element padding with `!important`, so
  a plain rule — even at higher specificity — cannot override it.
