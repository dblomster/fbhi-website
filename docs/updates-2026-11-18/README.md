# Update round 2026-11-18

Status: **closed, green — dev and prod.** Same procedure as
[2026-11-06](../updates-2026-11-06/README.md): `novamira/execute-php` + `Plugin_Upgrader::bulk_upgrade` per batch,
`delete_site_transient('update_plugins')` + `wp_update_plugins()` before each batch. No emails (Daniel's rule).
New this round: WordPress **7.1.5** (minor), done the same way as the 7.1 update in
[2026-09](../archive/updates-2026-09/README.md) (`Core_Upgrader::upgrade(find_core_update(...))`).

## Dev (fbhi.devcx.com)

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| A (inactive on dev) | MailPoet | 5.45.1 | 5.45.2 |
| A (inactive on dev) | Redis Object Cache | 3.0.0 | 3.0.1 |
| B | All in One SEO | 5.0.5 | 5.0.5.1 |
| B | FluentCommunity (forum demo, dev only) | 2.14.0 | 2.15.0 |
| B | MapPress | 2.95.10 | — (no package, licence as always on dev) |
| D | The Events Calendar | 6.18.3.1 | 6.18.4 |
| Core | WordPress | 7.1.4 | **7.1.5** (db_version 61833 unchanged) |

No WPML update this round (no batch C). Left as before: dev-only plugins Jetpack Boost (4.7.1 → 4.8.2 offered),
LiteSpeed Cache, WP Mail SMTP, WP Super Cache. Redis Object Cache is inactive on dev but active on prod, so it was
updated here as a rehearsal.

### Regression on dev

- 8 pages 200, no PHP errors. HTML diffs (token level) show only version strings (WP 7.1.5, AIOSEO 5.0.5.1,
  TEC 6.18.4 / common 6.14.1), WPML cookie timestamps and per-request ids (core image `data-wp-key`, TEC breakpoint UUID).
- Counts unchanged: 15 forms, 10 events, 36 network-projects, 2 kommuner, 6 guides, 23 active plugins;
  `icl_translations` 1724, strings 32702, 38 `icl_*` tables, WPML default `en`, front page 6998; FluentCommunity
  16 tables, DB 1.1.6; Autoptimize `js/jquery/jquery.min.js` exclude still set.
- Chrome as visitor: console empty on home, calendar ("no upcoming events" before and after, dev events are all past),
  IWG form page (form 10430: 13 fields, `novalidate`, validator attached, `wpforms_settings`; not submitted), guide page.
  `/sv/forum/` → 302 to the portal's own login, as before.
- Admin: guide 13901 in the block editor, 30 blocks all valid, not dirty, AIOSEO sidebar present; console only the
  usual deprecation warnings (MapPress block API v1, `wp.editPost.PluginDocumentSettingPanel`, global-styles iframe note).

Captures: `~/fbhi-baselines/dev-baseline-2026-11-18/` (urls.txt, html, html-after).

## Prod (fbhi.se)

Done 2026-11-18 ~21:10–21:40 UTC after Daniel's full backup. No emails sent or triggered.

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| B | All in One SEO | 5.0.5 | 5.0.5.1 |
| B | MapPress | 2.97.16 | 2.97.17 |
| R | Redis Object Cache | 3.0.0 | 3.0.1 (drop-in 3.0.0 → 3.0.1) |
| D | The Events Calendar | 6.18.3.1 | 6.18.4 |
| D | MailPoet | 5.45.1 | 5.45.2 (DB 5.45.2) |
| — | MailPoet Premium | 5.45.0 | — (no newer release offered; same 5.45.x pairing as last round) |
| — | WordPress | 7.1.5 | — (auto-updated itself earlier the same day) |
| W | WPForms | 2.0.4 | **2.0.5** (via wp-admin, see below) |

**WPForms 2.0.5** (released 2026-11-17) was missed by the execute-php update check: WPForms only adds its offer in
wp-admin, where Dashboard → Updates showed it. Changelog: three security fixes (form location links, Dynamic
Choices, payment display) and a fix for a theme colour with CSS code restyling the whole page. Also: form styling moves
from the Form Builder's Themes panel to an "Edit Theme" button on the front end, "Disable Email Summaries" moves to
Settings → Email, plus built-in Drip, file-upload rules and bulk export of selected entries (unused here). Not
rehearsable on dev (licence). Updated with Daniel's OK via Dashboard → Updates ("All updates have been completed"),
then purge + cache flush. Afterwards: 2.0.5 active, no admin notices, no console errors on the Forms screen, 24 forms
/ 3432 entries. IWG form 14741: same 25 real fields in the same order, honeypots `field_1` + `field_2`, `novalidate`,
validator attached, console clean (not submitted). The only HTML change is a new `wpforms-embed-<hash>` class on the
form container (for per-embed themes) plus asset versions. The seminar page is unchanged. Dashboard → Updates is now empty.

The standing procedure, including the wp-admin check for WPForms, is now in [../update-procedure.md](../update-procedure.md).

How it went: B and R clean. **Redis drop-in:** the plugin doesn't replace `wp-content/object-cache.php` during the
upgrade itself. It does that on the next wp-admin load (`admin_init` → `maybe_update_dropin` → copy on `shutdown`,
unless `WP_REDIS_DISABLE_DROPIN_AUTOUPDATE`). Here it happened at 21:11:09 UTC. Afterwards 3.0.1, connected,
`WP_REDIS_GRACEFUL` still true. Batch D returned the usual **Nginx 504**. A read-only check showed both done, no
`.maintenance`, empty upgrade folders. Then Nginx Helper purge-all (usual unlink warnings) + `wp_cache_flush()`.

### Regression (prod, logged-out visitor)

- 8 pages 200, no PHP errors. The first uncached load of the network-project page timed out once after the purge;
  a retry gave 200 in 2 s.
- HTML diffs (token level, minus Salient `fws_` ids, TEC UUIDs, cache-buster): only version strings (AIOSEO
  5.0.5.1, MapPress 2.97.17 CSS, TEC 6.18.4 / common 6.14.1) and Autoptimize `ver` timestamps. Two harmless extras:
  - IWG form: same 25 real fields in the same order; only the WPForms honeypot `field_1` sits at a different
    position (random per render by design since 2.0.4).
  - home-en got a regenerated Complianz banner CSS (`cmplz-banner-1-optin`) without its two
    `.wpforms-recaptcha-container` placeholder rules. The other pages kept the full file, and reCAPTCHA isn't used.
- Counts unchanged (see pre-check below); Redis connected; no MailPoet sending task due.
- Chrome as visitor: console empty on home, IWG form (14741: `novalidate`, validator attached, `wpforms_settings`;
  not submitted) and calendar.
- Admin (one-time access link, authorised by Daniel): guide 15375 in the block editor, 53 blocks all valid, not dirty,
  AIOSEO sidebar present. Console only the global-styles iframe warning, no errors (the AIOSEO chunk failures from last
  round did not recur). Closed without saving.
- MapPress: prod has a single map in `mapp_maps` and no post content embeds a MapPress block or shortcode, so there was
  no map page to check visually.

Captures: `~/fbhi-baselines/prod-baseline-2026-11-18/` (urls.txt, html, html-after).

**Pre-check (read-only, 2026-11-18 ~21:10 UTC):** WordPress is **already 7.1.5**: core auto-updated itself
today (~18:00 UTC; the success notice went to Annika's address). PHP 8.3.36, 25 active plugins, no `.maintenance`.
Offers: AIOSEO 5.0.5 → 5.0.5.1, MailPoet 5.45.1 → 5.45.2, MapPress 2.97.16 → 2.97.17, Redis Object Cache
3.0.0 → 3.0.1 (drop-in 3.0.0, connected, `WP_REDIS_GRACEFUL` true), TEC 6.18.3.1 → 6.18.4. No WPForms or WPML update.

MailPoet: **no sending task due.** The long-paused task 5310 was deleted 2026-11-18 09:42 UTC (Annika's cleanup).
Today's sending tasks (her test sends) are all completed. The only task with status NULL is 2437, type `beamer`
(MailPoet's in-app news fetch, stuck since 2024-12, sends nothing).

Baseline counts: 24 forms / 3432 entries, 5 events, 43 network-projects, 13 kommuner, 5 guides, 871 MailPoet
subscribers; `icl_translations` 2120, strings 15039, 38 `icl_*` tables, WPML default `en`, front page 6998;
Autoptimize jQuery exclude set. 8 baseline pages 200 (`x-cache: BYPASS` with a cache-busting query), no PHP errors.