# FINGER guide go-live checklist (planned ~2026-10-12)

Recorded 2026-10-01. Run this **read-only on prod** around the go-live date: check and report, change nothing.
Every write (deleting media, publishing, menu item, settings) is a separate step that Daniel authorises per
operation, or that editors do in wp-admin themselves. Prod rules: CLAUDE.md → "Novamira MCP (PRODUCTION)".

Prod state when recorded: guide on prod **as drafts** since 2026-09-24 (root 15374 `/sv/guide/finger/`, chapters
15375–15379, `sv`), no menu item. ID table: [../updates-2026-09-24/README.md](../updates-2026-09-24/README.md)
§ Guide copy log.

## 1. Due regardless of the guide

- [ ] **WPForms "corrupted post data"**: Phase 2 monitoring ran through the 30 Sep seminar. Check WPForms →
  Tools → Logs on prod (or read the WPForms log table via execute-php, read-only) for "corrupted post data"
  entries since the Autoptimize jQuery-exclude fix on 2026-09-24. None → close the issue, Phase 3 fallback not
  needed. Plan + change log: [../wpforms-corrupted-post-data/2026-09-plan.md](../wpforms-corrupted-post-data/2026-09-plan.md).

## 2. Before publishing the guide

- [ ] **Example media**: list attachments named `exempel-handbok-*` on prod (15354–15373, an en+sv WPML pair per
  file) and which guide pages still use them. They must be deleted or replaced before publishing.
- [ ] **Content**: the prod drafts are copies of dev from 2026-09-24. Check whether Annika's real content is in
  (dev or prod, wherever she edited) and whether placeholders remain ("XX", "kapitlet om xxx", "finns i här").
- [ ] **Chapter 1** (13901 dev / its prod copy): no H3 promotion or callouts yet, unlike chapters 2–4. Check whether
  Annika has handled it by hand.
- [ ] **Chapter 5 colour**: navy's tint is close to teal's, so the cards look alike. Decision still open.
- [ ] **Password protection**: if any guide page goes live password-protected, first check that prod's Nginx
  cache bypasses the `wp-postpass_*` cookie. Read-only test: on any published prod page, compare the cache
  response header with and without a dummy `wp-postpass_…` cookie set in the browser (Chrome fetch; curl is
  WAF-blocked). See [../password-protection.md](../password-protection.md).
- [ ] **Image/video handling** (parked until real content): with Annika's images in, check desktop, mobile and
  print (fixed height without an aspect ratio squashes on mobile). Details: README.md → Outstanding.
- [ ] **Menu item + publish order**: who adds the Swedish menu item and publishes root + chapters (Daniel/editors).
  After publishing: purge the Nginx cache (Nginx Helper) and check the guide as a logged-out visitor.

## 3. Housekeeping

- [ ] **Dev guide passwords**: every dev guide page still has the test password from 2026-09-28. Remove when the
  test is over (dev write, confirm first).
- [ ] **Prod FAQ redirect**: the page was renamed to `/sv/fragor-och-svar-om-finger/`; the old
  `/sv/fragor-och-svar/` returns 404. Maybe add a redirect.
- [ ] Dev only: the TEC Month link renders as a regex (404) after cache rebuilds. Prod unaffected.

## 4. Parked, not blocking go-live

- Short "how to edit the guide" note for editors. Include the heading tip below.
- Guide listing page (Q9) and open design questions Q2, Q5–Q8 in [decisions.md](decisions.md) (defaults in place).

## Editor questions handled so far

- **2026-10-01, Paragraph → Heading (Annika)**: the transform menu of a Paragraph shows only "Rubrik", without
  H1–H6, while a Heading shows the levels. Not a restriction: investigated on dev (no locks, `templateLock`,
  `contentOnly` or `allowedBlocks` anywhere; the guides' allowed-block list includes `core/heading`). In WP 7.x
  core lists level variations only for the block that is already selected. How to: choose "Rubrik" (gives an H2),
  then pick H3 in the toolbar; or type `### ` at the start of an empty paragraph. Explained to Annika by Daniel.
