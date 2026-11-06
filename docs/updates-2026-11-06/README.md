# Update round 2026-11-06

Status: **closed, green — dev and prod.** WordPress is already current on both
(7.1.4), Salient has no licence (stays 15.0.9). Same procedure as [2026-10-20](../updates-2026-10-20/README.md):
`novamira/execute-php` + `Plugin_Upgrader::bulk_upgrade` per batch, `wp_update_plugins()` first. No emails (Daniel's
rule): MailPoet's only sending task is still 5310, paused since 2026-03-19 (checked on prod before the round).

## Dev (fbhi.devcx.com)

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| A (inactive on dev) | MailPoet | 5.42.0 | 5.45.1 |
| B | ACF | 6.8.10 | 6.8.11 |
| B | All in One SEO | 5.0.3 | 5.0.5 |
| B | Autoptimize | 3.1.16 | 3.1.17 |
| B | Complianz | 7.5.6 | 7.5.7 |
| B | FluentSMTP | 2.4.1 | 2.4.5 |
| B | MapPress | 2.95.10 | — (licence, as always on dev) |
| C | WPML Multilingual CMS | 5.1.0 | **5.3.0** |
| C | WPML String Translation | 5.1.0 | **5.3.0** |
| D | The Events Calendar | 6.18.2 | 6.18.3.1 |

WPML was listed as 5.2.0 in the first check; the fresh `wp_update_plugins()` in batch C offered 5.3.0. Left as
before: dev-only plugins (Jetpack Boost, LiteSpeed Cache, WP Mail SMTP, WP Super Cache). WPForms cannot update on
dev (licence; dev is still on 1.9.8.7), so **WPForms 2.0.4 is not rehearsed** — prod only.

### What WPML 5.3 changes

- **Removes WPML's internal "Translation Priority" taxonomy** (upgrade command `RemoveTranslationPriorityTaxonomy`):
  its terms, their 8 `icl_translations` rows (1729 → 1721), 3 label strings (32658 → 32655) and, in a later
  deferred step, the one completed sv translation of its "URL translation_priority tax slug" string (838 → 837).
  Nothing of the site's content; no orphaned string translations left.
- New tables (34 → 38 `icl_*`), e.g. `icl_term_batch_members`.
- Same "WPML is finalizing the database update … (2 steps left)" notice as with 5.1; gone after 3 admin page loads.
- **Language switcher**: the current-language item ("EN"/"SV") is now `role="button"` with `aria-expanded` that
  actually toggles, driven by a new inline script (`wpml-ls-menu-a11y-js`, ~5 KB, inlined by Autoptimize, which is
  why every page is ~5.6 KB bigger). Hover dropdown looks and works as before; the other language's link is a normal link.

### Regression on dev

- 8 pages 200, no PHP errors, right `lang`; HTML diffs only version strings, Salient row ids, the WPML switcher
  change above, and a small inline Complianz rule (`.cmplz-cookiebanner .cmplz-close svg` 20×20).
- WPML: default `en`, languages `en,sv`, front page 6998 ↔ 9798; menus Main 22 / Main (swedish) 31.
- Counts unchanged: 15 forms, 10 events, 36 network-projects, 2 kommuner, 6 guides; 22 active plugins.
- Chrome as visitor: console clean on home, calendar, IWG form page (form 10430: 13 fields, `novalidate`, validator
  attached, `wpforms_settings` present; not submitted), guide page. Admin: guide 13901 in the block editor, 30 blocks
  all valid, console clean. Admin notices otherwise as before (MapPress/WPForms licence on dev, WPML pricing notice).
- Noticed, **not from this round**: the Complianz cookie banner showed stray list bullets around "Privacy policy"
  (bottom row), same on prod before the update. Cause: Salient's global `ul li { list-style: outside disc }` reaches
  the banner's `<li>`s (Complianz resets only the `<ul>`); the two hidden `{title}` placeholder items got a bullet
  each too. **Fixed** in `css/custom.css` (group 1, `.cmplz-cookiebanner .cmplz-links li { list-style: none }`),
  on dev 2026-11-06, checked in both banner states (default and "View preferences").

Captures: `~/fbhi-baselines/dev-baseline-2026-11-06/` (urls.txt, html, html-after).

## Prod (fbhi.se)

Done 2026-11-06 ~03:00–04:00 UTC after Daniel's full backup. No emails sent or triggered; MailPoet task 5310 still paused.

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| B | ACF | 6.8.10 | 6.8.11 |
| B | All in One SEO | 5.0.3 | 5.0.5 |
| B | Autoptimize | 3.1.16 | 3.1.17 |
| B | Complianz | 7.5.6 | 7.5.7 |
| B | FluentSMTP | 2.4.1 | 2.4.5 |
| B | MapPress | 2.97.14 | 2.97.16 |
| B | WPForms | 2.0.2.2 | **2.0.4** |
| C | WPML Multilingual CMS | 5.1.0 | **5.3.0** |
| C | WPML String Translation | 5.1.0 | **5.3.0** |
| D | The Events Calendar | 6.18.2 | 6.18.3.1 |
| D | MailPoet | 5.42.0 | 5.45.1 |
| D | MailPoet Premium | 5.42.0 | 5.45.0 (offered only after MailPoet, as before) |

Before batch B: `WP_REDIS_GRACEFUL` added to `wp-config.php` (see Redis below) so a Redis refusal could not kill an
upgrade request halfway. Then the cookie-banner fix via `./upload.sh p` (only `css/custom.css`), Nginx Helper
purge-all (usual unlink warnings) + `wp_cache_flush()`.

How it went: batch B returned the usual **Nginx 504**; a read-only check showed all 7 done, no `.maintenance`, empty
`upgrade` / `upgrade-temp-backup`. Batch C: after the CMS upgrade the `update_plugins` transient was empty, so the
ST upgrade returned `true` without doing anything — rerun with `delete_site_transient('update_plugins')` +
`wp_update_plugins()` first (offer said 5.2.0, WPML delivered 5.3.0, same as dev). WPML's "finalizing (2 steps
left)" notice cleared after 3 admin page loads.

**WPForms 2.0.4** (new, not rehearsed on dev): admin notice "We upgraded 23 forms from legacy anti-spam to modern
anti-spam protection". Visible effect: forms now carry honeypot fields (IWG form: `field_1` server-side + `field_2`
added by JS, random labels like "Study the Please", 1×1 px, `position:absolute`, `z-index:-1000`, `tabindex=-1`).
Invisible for visitors; screenshots identical.

### Regression (prod, logged-out visitor, 1400px)

- 8 pages 200, no PHP errors, right `lang`, `x-cache: MISS` after the purge; console empty on all 8.
- Full-page screenshots before/after: **identical except 75 px** in the same strip on every page — the three cookie
  banner bullets that the fix removed.
- HTML diffs: version strings, the WPML switcher `role="button"` + a11y script, the Complianz close-icon rule,
  WPForms honeypot. Titles, meta descriptions, canonicals, hreflang identical on all 8.
- IWG form 14741: `novalidate`, jQuery validator attached, `wpforms_settings` present (not submitted). The seminar
  page (30 Sep) no longer has a form.
- WPML: default `en`, languages `en,sv`, front page 6998 ↔ 9798, menus Main 21 / Main (swedish) 25;
  `icl_translations` 2090 → 2071 (exactly the 19 Translation Priority rows), strings 15016 → 15013, completed string
  translations 871 → 871, `icl_*` tables 34 → 37.
- Counts unchanged: 23 forms / 3246 entries, 5 events, 43 network-projects, 11 kommuner, 5 published guides, 780
  MailPoet subscribers, 25 active plugins; MailPoet DB 5.45.1; Autoptimize `js/jquery/jquery.min.js` exclude still set.
- Admin: guide 15375 opens in the block editor, all blocks valid, not saved. On the first two loads AIOSEO's lazily
  imported script chunks failed (3× HTTP 500, then "Failed to fetch dynamically imported module" for 5 others);
  the same files return 200 with curl and the third load was clean. Most likely a short-lived burst limit while the
  freshly updated files were first requested; watch for it if editors report AIOSEO sidebar glitches.

Captures: `~/fbhi-baselines/prod-baseline-2026-11-06/` (urls.txt, html, html-after, screens, screens-after).

## Redis error on prod (Annika, 2026-11-06)

Annika: "Error establishing a Redis connection — OOM command not allowed when used memory > 'maxmemory'" sometimes
when opening a guide page for editing; a reload helps. Prod only (dev has no Redis object cache).

Findings (read-only, 2026-11-06 ~02:40 UTC):

- Redis 8.10.2 via unix socket, `maxmemory` **19 MB** (host-set), policy `allkeys-lru`; 17.8 MB used, peak 23.2 MB,
  41 345 keys evicted in 4 days of uptime. So it is permanently full and lives on eviction.
- 6 376 keys / 16.5 MB: `posts` group 8.5 MB (each guide page and revision ~64 KB), `post_meta` 2.3 MB, `options`
  2.1 MB; biggest single keys `monsterinsights_notifications` 0.9 MB, WPML `convert_url` group keys 0.8 MB,
  `alloptions` 0.8 MB.
- **6 guide pages have 653 revisions** (~110 each, `WP_POST_REVISIONS` unlimited). Opening the editor loads them,
  i.e. a burst of large writes; when eviction can't make room in time Redis answers OOM.
- Redis Object Cache 3.0.0 only fails gracefully when `WP_REDIS_GRACEFUL` is true
  (`$fail_gracefully = defined( 'WP_REDIS_GRACEFUL' ) && WP_REDIS_GRACEFUL`); it was not defined, so any Redis
  exception ends in `show_error_and_die()` — the page Annika saw.

Done (with Daniel's OK): `define( 'WP_REDIS_GRACEFUL', true );` added to prod `wp-config.php` just above "That's all,
stop editing" (backup `wp-config.php.bak-2026-11-06` one level above `public_html`, not web-reachable; `php -l` clean).
A refused write now only skips the persistent cache for that request (logged via `error_log`) instead of the error page.
Verified: `fail_gracefully = true`, Redis still connected, site 200.

Not done, for Daniel to decide:

1. Ask the host whether Redis memory can be raised (19 MB is very small for WPML + TEC + guides).
2. Cap guide revisions (e.g. a `wp_revisions_to_keep` filter for `guide` in the child theme) and/or prune old ones
   — fewer large objects per editor load. Content decision (Annika may use the revision history).
3. Optional: MonsterInsights' 0.9 MB `monsterinsights_notifications` option.

Reply to Annika (cause in plain terms, error page fixed, revision cap not mentioned) **sent by Daniel 2026-11-06**.
Afterwards Daniel disabled Novamira on prod.
