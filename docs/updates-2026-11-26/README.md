# Update round 2026-11-26

Status: **closed, green — dev and prod.** Procedure: [../update-procedure.md](../update-procedure.md). No emails
(Daniel's rule).

## Dev (fbhi.devcx.com)

WordPress 7.1.5 (latest, no core update), PHP 8.4.28. Pre-check: no MailPoet sending task due, no `.maintenance`,
upgrade folders empty.

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| A (inactive on dev) | MailPoet | 5.45.2 | 5.46.0 |
| A (inactive on dev) | TablePress | 3.4.1 | 3.5 |
| B | Advanced Custom Fields | 6.8.11 | 6.8.12 (+ sv_SE translation) |
| B | All in One SEO | 5.0.5.1 | 5.0.6.1 |
| B | MapPress | 2.95.10 | — (2.98.1 offered, no package, licence as always on dev) |
| D | The Events Calendar | 6.18.4 | 6.18.4.1 (common 6.14.1.1) |

No WPML offer (no batch C). Left as before: dev-only plugins Jetpack Boost (4.7.1 → 4.9.0 offered), LiteSpeed Cache,
WP Mail SMTP, WP Super Cache. All batches clean in one call each.

### Regression on dev

- 8 pages 200, no PHP errors. HTML diffs (token level) show only version strings (AIOSEO 5.0.6.1, TEC 6.18.4.1 /
  common 6.14.1.1), a TEC timestamp, a WPML cookie timestamp and per-request ids.
- Counts unchanged: 15 forms / 2376 entries, 10 events, 37 network-projects, 2 kommuner, 6 guides, 934 MailPoet
  subscribers, `icl_translations` 1724, 38 `icl_*` tables, 16 FluentCommunity tables, WPML default `en`, front page 6998,
  23 active plugins, Autoptimize jQuery exclude still set.
  - Only change: `icl_strings` 32702 → 32743 (+41). All 41 are new `admin_texts_aioseo_options_dynamic_localized`
    strings that WPML registered after the AIOSEO update (ids 48091–48131). Harmless; expect the same on prod.
- Chrome as visitor: console clean (only the jQuery Migrate log + Chrome a11y hints) on home, calendar, IWG form page
  (form 10430: 13 fields, `novalidate`, validator attached, `wpforms_settings`; not submitted) and guide page.
- Admin: guide 13901 in the block editor, 30 blocks all valid, not dirty, AIOSEO sidebar present; console only the
  usual deprecation warnings (MapPress block API v1, `wp.editPost.PluginDocumentSettingPanel`, global-styles iframe
  note). Closed without saving.

Captures: `~/fbhi-baselines/dev-baseline-2026-11-26/` (urls.txt, html, html-after).

## Prod (fbhi.se)

Done 2026-11-26 ~01:15–01:45 UTC after Daniel's full backup. Novamira request `upd1126`. No emails sent or triggered.

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| B | Advanced Custom Fields | 6.8.11 | 6.8.12 (+ sv_SE translation) |
| B | All in One SEO | 5.0.5.1 | 5.0.6.1 |
| B | MapPress | 2.97.17 | 2.98 |
| B | TablePress (inactive on prod too) | 3.4.1 | 3.5 |
| D | The Events Calendar | 6.18.4 | 6.18.4.1 |
| D | MailPoet | 5.45.2 | 5.46.0 (DB 5.46.0) |
| D | MailPoet Premium | 5.45.0 | 5.46.0 (offered once MailPoet was updated) |
| W | WPForms | 2.0.5 | **2.0.5.2** (via wp-admin, Daniel's OK) |
| — | WordPress | 7.1.5 | — (already latest) |

**Pre-check:** WordPress 7.1.5, PHP 8.3.37, 25 active plugins, no `.maintenance`, upgrade folders empty. MailPoet: no
sending task due (only 2437 `beamer`). Redis connected, `WP_REDIS_GRACEFUL` true, drop-in 3.0.1 (no Redis update this
round). Baseline counts: 24 forms / 3461 entries, 5 events, 47 network-projects, 14 kommuner, 6 guides, 888 MailPoet
subscribers, `icl_translations` 2136, strings 15042, 38 `icl_*` tables, WPML default `en`, front page 6998, Autoptimize
jQuery exclude set. 8 baseline pages 200 (`x-cache: BYPASS`).

**How it went:** B clean in one call (20 s). D returned the usual **Nginx 504**. The next read-only call failed with
"fetch failed" (it read `mailpoet_settings` with raw SQL — WAF, see the quirks note). A trivial call then worked, and a
version check showed TEC 6.18.4.1 + MailPoet 5.46.0 active and the upgrade folders empty. MailPoet's DB version, read
through its `SettingsController`, was 5.46.0. After that MailPoet Premium 5.46.0 was offered and installed on its own.
Purge-all (errors swallowed) + `wp_cache_flush()`.

**WPForms 2.0.5.2** (2026-11-24) showed only in wp-admin → Dashboard → Updates, as expected. Changelog for 2.0.5.1 +
2.0.5.2: DOMPurify 3.5.0, Elementor editor / theme demo import fix, and a PayPal Commerce conditional-logic fix. None of
these touches anything fbhi uses. It was updated via Dashboard → Updates with only WPForms ticked ("All updates have
been completed"). Then purge + flush. Dashboard → Updates is empty afterwards.

### Regression (prod)

- 8 pages 200, no PHP errors. HTML diffs (token level) show only version strings (AIOSEO 5.0.6.1, TEC/common), plus
  the MapPress and WPForms Autoptimize CSS files under new names (new versions). On the IWG form the honeypot sits
  at a different position (random by design) and its timestamp token is new. The seminar page has only versions.
- Calendar: the before-capture had JSON-LD for three past test events and the after-capture had none. A fresh load
  afterwards had it again, and last round's captures had none before or after, so whether it's there varies from
  load to load. The update didn't cause it. The visible content is the same ("There are no upcoming events" + the
  same three "Latest Past Events"). Only the order of two test events with the same start time swapped. All 5 events
  are published March 2026 test events, none password-protected.
- Counts unchanged, apart from `icl_strings` 15042 → 15090 (+48). All 48 are new
  `admin_texts_aioseo_options_dynamic_localized` strings from the AIOSEO update, the same thing as on dev.
- Chrome as visitor: console empty on home, IWG form (14741: 27 fields = 25 real + 2 honeypots, `novalidate`,
  validator attached, `wpforms_settings`; not submitted; rechecked on 2.0.5.2), calendar and guide page
  (`/sv/guide/finger/bakgrund/`).
- Admin (one-time access link, authorised by Daniel): guide 15375 in the block editor, 53 blocks all valid, not
  dirty, AIOSEO sidebar present, console only the global-styles iframe warning. Closed without saving.

Captures: `~/fbhi-baselines/prod-baseline-2026-11-26/` (urls.txt, html, html-after, html-after-wpforms).
