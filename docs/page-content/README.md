# page-content

Reference copies of content that lives **in WordPress** rather than in the
theme: WPBakery page shortcodes, Customizer ("Additional CSS") snippets, and
similar per-page markup/styles.

Why keep it here:

- It is **tracked in git**, so this otherwise-invisible WP-managed content is
  reviewable, diffable, and recoverable.
- It sits outside `salient-child/`, so `upload.sh` (which only rsyncs the theme)
  **never deploys it** — there is nothing here for production to pick up.

These files are documentation, not a source of truth: the running site is the
source of truth. When you change a page or its Customizer CSS in WP, update the
matching file here by hand to keep the reference current.

One subfolder per page/feature, e.g.:

```
page-content/
  faq-accordion/
    shortcode.txt
    customizer.css
    README.md
```
