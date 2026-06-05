# FAQ accordion page ("Frågor och svar om FINGER")

Reference copies of the WPBakery page content and Customizer CSS for the
accordion version of the FAQ page. These live in WordPress, **not** in the
theme — this folder is just a tracked, deployable-free copy so the markup and
styles are reviewable and diffable in git.

- `shortcode.txt` — the full WPBakery shortcode for the page body.
- `customizer.css` — the Additional CSS that styles the accordion.

## Pages

- Live (classic, non-accordion): https://fbhi.se/sv/fragor-och-svar-om-finger/
- Test (accordion):              https://fbhi.se/sv/fragor-och-svar-test/

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

3. **Styles** — paste `customizer.css` into
   **Appearance → Customize → Additional CSS** (alongside the other site CSS).

   > ⚠️ **Keep `customizer.css` comments free of HTML tags / angle brackets.**
   > Write `h4`, not the angle-bracket tag form. The WP Customizer "Additional
   > CSS" sanitizer rejects `<...>`-style text and will mangle the CSS that
   > follows such a comment.

After deploying/editing on production, purge the Nginx cache (Nginx Helper).

## Notes / knobs

- Bar look matches the classic page: `#fee6ac` fill, black 18px text, radius 3px.
- Question text colour can be switched to brand teal by changing `color: #000`
  to `color: #006885` in the `.toggle-heading` rule.
- Multiple panels can be open at once (typical for an FAQ). Single-open
  behaviour would be a Toggle-group setting, not CSS.

## CSS rule notes

The `customizer.css` comments are kept terse for production (the live copy is
pasted into the Customizer). The rationale lives here, rule by rule:

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
