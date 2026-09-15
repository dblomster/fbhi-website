# Update round — 2026-09 (dev rehearsal → prod)

Goal: bring WordPress core, plugins and (if licensable) the Salient theme up to date. Rehearse on
**dev (fbhi.devcx.com)** first, record everything, then repeat on **prod (fbhi.se)**.
Backups on both environments are taken **manually by Daniel** before any update step.

Previous round for reference: [updates-2026-08/](../updates-2026-08/README.md). **Round closed 2026-09-15** — archived.

## Status

- [x] 2026-09-15 dev inventory + compatibility assessment (below)
- [x] 2026-09-15 dev baseline captured: `~/fbhi-baselines/dev-baseline-2026-09-15/` (9 pages, HTML +
      full-page screenshots, console; see its `MANIFEST.md`)
- [x] 2026-09-15 Daniel: manual dev backup; Novamira updated manually
- [x] 2026-09-15 Dev plugin updates applied (batches A–D) + regression pass — **green, see results below**
- [x] 2026-09-15 Dev admin/editor checks — green (WPBakery, Salient options, guide editor, WPForms builder, FluentSMTP)
- [ ] **Daniel: manual check of dev** before prod
- [x] 2026-09-15 Daniel: manual check of dev — OK
- [x] 2026-09-15 Prod inventory via Novamira MCP (below)
- [x] 2026-09-15 Daniel: manual prod backup
- [x] 2026-09-15 Prod baseline captured: `~/fbhi-baselines/prod-baseline-2026-09-15/` (8 pages, HTML +
      full-page screenshots, console; see its `MANIFEST.md`)
- [x] 2026-09-15 Prod plugin batches A–D applied via `execute-php` (Daniel authorised each batch explicitly —
      the auto-mode classifier accepts an exact per-batch instruction, not a general "update prod")
- [x] 2026-09-15 Prod core 7.0.4 → 7.1, Nginx purge + Redis flush, final regression pass — **green**
- [x] 2026-09-15 Daniel, manually in wp-admin: WPForms 2.0.0.4 → 2.0.1.1 (offered again later the same day) and
      Novamira Pro 1.9.0 → 1.10.0. Verified via MCP: nothing pending, no paused plugins.
- [ ] Optional, any time: FluentSMTP 2.4.0 dashboard visual check in wp-admin (settings verified intact via PHP)

## Dev inventory (2026-09-15, via Novamira MCP / WP-CLI)

WordPress **7.1** (already latest; db_version 61833), PHP 8.4.24, dev has WP-CLI (prod does not).
Salient parent 15.0.9, salient-child 0.1, Salient Core 1.9.9, Salient WPBakery 6.9.2 — no theme
update offered (no license). `salient_redux`: `delay-js-execution = 0`, `defer-javascript = 0` (same
as prod). WP_DEBUG off, no debug.log.

### Plugins with updates available on dev

| Plugin | Status | Dev now | Available | Note |
| --- | --- | --- | --- | --- |
| Advanced Custom Fields | active | 6.7.0 | 6.8.10 | prod is on 6.8.7 |
| All in One SEO | active | 4.9.3 | 5.0.1.1 | **major**; prod already on 5.0.0.1 |
| Autoptimize | active | 3.1.14 | 3.1.15.1 | |
| Complianz | active | 7.4.4.2 | 7.5.5 | prod on 7.5.3 |
| Duplicate Page | active | 4.5.6 | 4.5.9 | |
| FluentSMTP | active | 2.2.95 | 2.4.0 | **admin UI rewrite** (Vue 3), requires WP ≥ 6.5; prod on 2.3.1 |
| Font Awesome | active | 5.1.3 | 5.2.1 | prod on 5.2.1 |
| MonsterInsights | active | 11.1.3 | 11.2.0 | prod on 11.1.2 |
| Jetpack Boost | active | 4.5.5 | 4.7.0 | |
| MapPress | active | 2.95.10 | 2.97.12 | prod on 2.97.9 |
| Novamira | active | 1.12.1 | 1.12.3 | **never via MCP** — Daniel updates manually |
| The Events Calendar | active | 6.15.17.1 | 6.17.4.1 | prod on 6.17.2 |
| WPForms | active | 1.9.8.7 | 2.0.1.1 | **major**; prod already on 2.0.0.4 |
| WPML Multilingual CMS | active | 4.8.6 | 4.9.7 | 4.9.7 = "full WP 7.1 compatibility + security hardening" |
| WPML String Translation | active | 3.4.1 | 3.5.4 | |
| LiteSpeed Cache | inactive | 7.7 | 7.9.1 | |
| MailPoet | inactive | 5.17.6 | 5.38.0 | |
| Nginx Helper | inactive | 2.3.5 | 2.4.1 | |
| Redirection | inactive | 5.6.0 | 5.10.0 | |
| Redis Object Cache | inactive | 2.7.0 | 2.8.0 | |
| TablePress | inactive | 3.2.6 | 3.3.4 | |
| WP Mail SMTP | inactive | 4.7.1 | 4.9.0 | |
| WP Migrate Lite | inactive | 2.7.7 | 2.7.11 | |
| WP Super Cache | inactive | 3.0.3 | 3.1.3 | |

No update offered for: Salient Core/WPBakery/Portfolio/etc. (license), Novamira Pro, Events Calendar
Pro (inactive), WPCodeBox 2.

## Compatibility assessment

### WordPress 7.1 (released 2026-08-19)

- Dev already runs 7.1 with Salient 15.0.9 and the whole plugin stack: front-end clean on all 9
  baseline pages, zero JS errors.
- Developer-facing changes (Field Guide): **jQuery UI bumped to 1.14.2** (test WPBakery backend
  editor and Salient theme options on dev — they lean on jQuery UI), post editor is now always
  iframed (relevant for Gutenberg-only `guide` CPT — verify the guides editor), conditional block
  CSS output, REST attachment endpoint tweaks. No PHP-minimum change, nothing that touches classic
  themes specifically.
- Prod core step will be **7.0.4 → 7.1.x**.

### Salient theme

- Latest is **18.2.1 (2026-06-01)**. 18.2.0 "Added WP 7.0 Compatibility"; 18.2.1 fixed the
  Delay-JS logic for WP 7.0+. **No changelog entry for WP 7.1 yet**, so even the newest Salient is
  only "7.0-certified"; 15.0.9 vs 18.2.1 makes no difference for official 7.1 status.
- The one known WP 7.0+ Salient bug (Delay-JS) is in a feature disabled on both envs.
- Conclusion: **15.0.9 stays** unless the ThemeNectar license is renewed. Running 15.0.9 on 7.1 is
  "works in practice, officially unsupported" — same posture as the August core update, and dev
  is the running proof. If the license is renewed, the 15 → 18 jump (theme + Core + WPBakery
  together) is a separate project: audit `header.php` override and Salient internals used by
  the child theme first, rehearse on dev.

### Plugins — risk notes

- **WPForms 1.9.8.7 → 2.0.1.1** and **AIOSEO 4.9.3 → 5.0.1.1** are majors on dev, but prod
  already crossed both in August with zero regressions (only additive AIOSEO auto-descriptions).
- **FluentSMTP 2.4.0**: full admin redesign; mail sending path unchanged, Bcc fix for Office 365.
  Check the connection still shows as configured and send a test mail from its settings page.
- **WPML 4.9.7**: explicitly WP-7.1-compatible + security fix — the most important plugin update
  of this round for prod. Update WPML CMS **before** String Translation.
- **The Events Calendar 6.17.x**: August finding — `/calendar/` list page no longer emits aggregate
  Event JSON-LD (singles still do). Expected, not a regression.
- Everything else is minor/patch.

## Dev update plan (needs Daniel's go-ahead after manual backup)

Tooling: `wp plugin update <slug>` via `novamira/run-wp-cli` (dev has WP-CLI), purge LiteSpeed cache,
then the regression pass from the baseline `MANIFEST.md`.

1. **Batch A — inactive plugins** (zero front-end risk): tablepress, wp-migrate-db, redirection,
   redis-cache, mailpoet, nginx-helper, litespeed-cache, wp-mail-smtp, wp-super-cache.
2. **Batch B — low-risk active**: advanced-custom-fields, autoptimize, complianz-gdpr, duplicate-page,
   font-awesome, google-analytics-for-wordpress, jetpack-boost, mappress-google-maps-for-wordpress.
   → quick front-end check (home en/sv + one CPT single).
3. **Batch C — WPML**: sitepress-multilingual-cms, then wpml-string-translation.
   → full regression pass (language switcher, hreflang, sv/en pages).
4. **Batch D — majors / content-critical**: the-events-calendar, wpforms, all-in-one-seo-pack,
   fluent-smtp. → full regression pass + WPForms JS init check + FluentSMTP test mail + AIOSEO
   meta diff.
5. **Novamira 1.12.3** — Daniel, manually (not via MCP).
6. Admin checks with a Novamira admin-access link in Chrome: WPBakery backend editor on a
   throwaway draft, Salient theme options save, guide post in Gutenberg (iframed editor),
   WPForms builder opens form 11048.

## Prod plan (after dev is green)

Prod has no WP-CLI: updates via wp-admin (Dashboard → Updates). Order: manual backup → baseline
(same 8 pages as August + a guide page is N/A on prod) → plugin batches A–D (prod deltas are
smaller: mostly patch versions since August, plus FluentSMTP 2.4.0 and WPML 4.9.7) → core
7.0.4 → 7.1.x → purge Nginx cache (Nginx Helper) → regression pass.

## Prod inventory (2026-09-15, via Novamira MCP, read-only)

WordPress **7.0.4** (db_version 61833), PHP 8.3.33, core update offered: **7.1**. Salient 15.0.9 /
child 0.1, no theme update. `DISALLOW_FILE_MODS` off, `AUTOMATIC_UPDATER_DISABLED` off, filesystem
method `direct`, plugin auto-updates off, core major auto-update disabled. No WP-CLI, no `run-wp-cli`
ability on prod (only `execute-php`, file tools, admin-access-link). WP_DEBUG off, no debug.log.
Object cache drop-in present (Redis Object Cache 2.8.0 active); Nginx Helper 2.3.5 active,
fastcgi purge via GET, purge-on-edit enabled.

Differences from the plan's assumptions: **MailPoet 5.35.1 + MailPoet Premium 5.35.0 are active**
(inactive on dev); **FluentSMTP uses the PHP `mail()` provider** (sender info@fbhi.se, 1 mapping,
logging on) — there is no SMTP host to re-verify, so the "real SMTP connection" note does not apply;
WPML is already 4.9.6.

Counts for the regression pass: 23 forms / 3128 entries, 5 published events, 10 997 string
translations, 43 network-projects, 5 kommuner.

### Plugins with updates available on prod (13)

| Plugin | Active | Prod now | Available | Batch |
| --- | --- | --- | --- | --- |
| TablePress | no | 3.3.3 | 3.3.4 | A |
| Advanced Custom Fields | yes | 6.8.7 | 6.8.10 | B |
| Complianz | yes | 7.5.3 | 7.5.5 | B |
| MonsterInsights | yes | 11.1.2 | 11.2.0 | B |
| MapPress | yes | 2.97.9 | 2.97.12 | B (licence covers fbhi.se) |
| Nginx Helper | yes | 2.3.5 | 2.4.1 | B |
| WPML Multilingual CMS | yes | 4.9.6 | 4.9.7 | C (first) |
| WPML String Translation | yes | 3.5.3 | 3.5.4 | C (second) |
| The Events Calendar | yes | 6.17.2 | 6.17.4.1 | D |
| WPForms | yes | 2.0.0.4 | 2.0.1.1 | D |
| All in One SEO | yes | 5.0.0.1 | 5.0.1.1 | D |
| FluentSMTP | yes | 2.3.1 | 2.4.0 | D |
| MailPoet | yes | 5.35.1 | 5.38.0 (requires WP 7.0) | D — check MailPoet Premium offers a matching update afterwards |

Not offered: Novamira (already 1.12.3), Novamira Pro, Salient plugins (licence), Redis Object Cache,
Autoptimize, Duplicate Page, Font Awesome, WP Migrate Lite (all current).

## Prod log

- 2026-09-15 — prod MCP reachable again. Read-only inventory + baseline done (see above). First write
  attempt (`execute-php` running `Plugin_Upgrader::bulk_upgrade` for batch A+B, exactly what
  Dashboard → Updates does) was **denied by the Claude Code auto-mode classifier** ("Production
  Deploy"), and so was `create-admin-access-link` (which would have allowed clicking the updates in
  wp-admin from Chrome). Stopped and handed the choice to Daniel. Test mail from FluentSMTP is
  **not** to be sent without Daniel's explicit go (his rule: no emails without green light).

- 2026-09-15 — Daniel explicitly authorised batch A+B → applied via `execute-php` (`Plugin_Upgrader::bulk_upgrade`,
  40 s, maintenance mode on/off cleanly): TablePress 3.3.4, ACF 6.8.10 (+ sv_SE translation), Complianz 7.5.5,
  MonsterInsights 11.2.0, MapPress 2.97.12, Nginx Helper 2.4.1. Quick check: 8 pages 200, HTML diffs only
  version strings / Complianz cache-busters / Salient per-render `fws_` ids (`html-b/`), no paused plugins,
  no recovery mode, home-en console clean in Chrome, cookie banner + switcher present.

- 2026-09-15 — Daniel authorised batch C → `execute-php` upgrade of WPML CMS 4.9.6 → 4.9.7 then ST 3.5.3 → 3.5.4.
  The MCP call itself came back as an **Nginx 504** (WPML packages are large; PHP kept running past the
  proxy timeout) but the upgrade finished cleanly server-side: both versions installed + active, no
  `.maintenance` file, `wp-content/upgrade/` empty, no paused plugins. Regression (`html-c/`): 8 pages 200,
  only diff is the WPML generator tag (+ TEC per-request UUID on /calendar/); languages en+sv, 10 997 string
  translations unchanged, kommuner posts `sv`, en→sv post lookups resolve, hreflang en/sv/x-default, switcher
  OK, home-sv console clean in Chrome. Lesson: for big packages expect a 504 from the MCP — verify state with a
  read-only call instead of re-running the upgrade.

- 2026-09-15 — Daniel authorised batch D → three `execute-php` calls: (1) AIOSEO 5.0.0.1 → 5.0.1.1 + FluentSMTP
  2.3.1 → 2.4.0 (14 s, clean); **WPForms stayed 2.0.0.4** — the fresh update check no longer offered 2.0.1.1
  (licence is valid: basic, active sub, not expired, limit not reached; probably a staged rollout on the WPForms
  API — re-check Dashboard → Updates in a day or two). (2) TEC 6.17.2 → 6.17.4.1 + MailPoet 5.35.1 → 5.38.0
  (504 from the MCP again, finished cleanly server-side). (3) MailPoet Premium 5.35.0 → 5.38.0 (offered only
  after the core MailPoet update; 3.5 s) to remove the version mismatch. Gotcha: after a `bulk_upgrade` the
  `update_plugins` transient is cleared, so the next call must run `wp_update_plugins()` first or the upgrader
  reports "already at the latest version".
  Regression (`html-d/`): 8 pages 200; only AIOSEO generator tag changed (+ TEC per-request UUID, ordering of
  IDs in the child theme's WW-Fingers inline script). WPForms page: 19 fields, novalidate, validate + ajax
  settings present, AIOSEO description/og/schema present, console = baseline. 23 forms / 3128 entries,
  5 events, TEC migration "not required", FluentSMTP 1 connection (PHP mail) intact. **No test mail sent**
  (Daniel's rule). Fresh update check now also offers **Novamira Pro 1.10.0** — Daniel, manually.

- 2026-09-15 — Daniel authorised core → `execute-php` with `Core_Upgrader::upgrade(find_core_update('7.1','en_US'))`.
  First two attempts were rejected by **Nginx with 405** before reaching PHP — the request body contained a
  read of `wp-includes/version.php` (`file_get_contents` + that path); a trivial call worked, so it is a
  body-pattern rule on the host. Without the file read the upgrade ran (504 again, finished server-side):
  WordPress **7.1**, db_version 61833 (unchanged, as on dev), no `.maintenance`, `wp-content/upgrade/` empty,
  `get_core_updates()` = "latest". Then `do_action('rt_nginx_helper_purge_all')` + `wp_cache_flush()`:
  Redis flushed; Nginx Helper logged "unlink(...tmp/fastcgi/0): Operation not permitted" warnings from its
  directory sweep, but uncached URLs then served 7.1 markup with `x-cache: MISS`, so the cache was effectively
  refreshed. Final regression vs baseline (`html-after/`, `screens-after/`): green — details in the baseline
  `MANIFEST.md`. Summary: all 8 pages 200, only generator/version/WP-7.1-core-CSS diffs, aggregate Event
  JSON-LD gone from `/calendar/` (singles keep it; known TEC 6.17.x), 6 screenshots pixel-identical, home-sv
  scroll-section artifact only, zero console errors, WPForms JS init = baseline, counts unchanged (23 forms /
  3128 entries, 5 events, 10 997 strings, 43 network-projects, 5 kommuner, 779 MailPoet subscribers).

### Prod final state (2026-09-15)

WordPress 7.1, PHP 8.3.33, Salient 15.0.9. Plugins: ACF 6.8.10, AIOSEO 5.0.1.1, Complianz 7.5.5, FluentSMTP
2.4.0, MonsterInsights 11.2.0, MailPoet 5.38.0 + Premium 5.38.0, MapPress 2.97.12, Nginx Helper 2.4.1,
TablePress 3.3.4, TEC 6.17.4.1, WPML 4.9.7 / ST 3.5.4, WPForms 2.0.1.1, Novamira 1.12.3 / Pro 1.10.0.
Nothing pending. No test mail sent. Guides CPT not deployed to prod.

## Dev results (2026-09-15)

Applied via `wp plugin update` over the Novamira MCP. 23 of 25 updated; two blocked by licensing on the
dev domain (not regressions, and not applicable to prod):

- **MapPress Pro 2.95.10 → 2.97.12: not updated** — "licence is not active for this site" (dev domain).
  Prod's licence covers fbhi.se and updated fine in August.
- **WPForms 1.9.8.7 → 2.0.1.1: not updated** — Basic licence has `is_limit_reached = true` (single-site,
  prod holds the activation), so no package URL is served on dev. Prod is already on 2.0.x.
  Side note: the licence record shows `expires = 2026-09-17` with `sub_status = active` (auto-renew
  presumably) — worth a glance in the WPForms account.

Everything else went to the versions in the inventory table (ACF 6.8.10, AIOSEO 5.0.1.1, Autoptimize
3.1.15.1, Complianz 7.5.5, Duplicate Page 4.5.9, FluentSMTP 2.4.0, Font Awesome 5.2.1, MonsterInsights
11.2.0, Jetpack Boost 4.7.0, TEC 6.17.4.1, WPML 4.9.7, WPML ST 3.5.4, and all 9 inactive plugins).
`wp plugin list --update=available` afterwards lists only mappress + wpforms.

### Regression pass (baseline `~/fbhi-baselines/dev-baseline-2026-09-15/`)

- **HTTP**: all 9 pages 200.
- **HTML diff** (normalised for `ver=`, nonces, cache-buster): identical structure on all pages;
  only changes are (a) AIOSEO 5.0 auto-generated `description` / og / twitter meta on pages without a
  manual one (same additive behaviour seen on prod in August), (b) Complianz banner category toggles
  are now `<button aria-expanded …>` instead of `<span>` (a11y improvement), (c) WPML language-switcher
  flag images get `loading="lazy"`, (d) Autoptimize noscript link ordering, (e) TEC list-view URLs lose
  a stray `?` / `&#038;` (`/calendar/list/?eventDisplay=past`). No content or layout diffs.
- **Screenshots** (Pillow pixel diff, threshold 24/255): 8 of 9 pages ≤ 0.01% (a 50×41 px
  anti-aliasing diff on a footer icon, visually identical); home-sv 1.82% — the "Kognitiv träning"
  scroll-triggered hand illustration rendered blank in the *baseline* stitched shot and fully after
  (known Salient full-page-screenshot artifact, not a regression).
- **Console**: zero JS errors on all 9 pages. The pre-existing "interactive element inside
  `<summary>`" issue (count 8) is **gone** on 8 pages — it came from the Complianz banner and is
  fixed by 7.5.5; only the guide page keeps 5 (its own accordions). WPForms page still has the one
  pre-existing "no label" notice.
- **WPForms front-end** (form 11048, still on 1.9.8.7): 12 fields, novalidate, jQuery-Validate + AJAX
  settings present — identical to baseline. 15 forms / 2376 entries intact.
- **WPML**: hreflang en/sv/x-default unchanged, 8 switcher items unchanged, generator tag 4.9.7,
  active languages en+sv, kommuner/guide posts keep `sv`, 29 105 string translations present.
- **FluentSMTP 2.4.0**: settings intact (1 connection, PHP mail(), 1 mapping); new Vue dashboard
  loads ("Emails sent 14, failed 0, active connections 1"), no console errors. *Prod has a real SMTP
  connection — re-verify it on the new dashboard and send a test mail there.*
- **TEC**: 10 events, migration state "not required", calendar list renders.
- **PHP**: no PHP warnings/notices surfaced through WP-CLI calls; no `debug.log` (WP_DEBUG off).

### Admin checks (Novamira admin-access link, isolated Chrome context, logged in as Daniel)

- **Dashboard**: WordPress 7.1, no PHP errors. Notices: MapPress licence, WPForms "no activations
  left", Salient recommended plugins, and the core "Events and News" widget saying "Ett fel uppstod"
  (community-events feed fetch failing from the dev box — unrelated to updates). Console clean.
- **Salient theme options** (`admin.php?page=SalientChildTheme&tab=1`): Redux loads (1237 fields,
  43 sections, jQuery UI 1.14.2 active), no console errors. "Save Changes" → "Settings Saved!",
  `changed_values = []`, `salient_dynamic_css_success = true`, homepage HTML byte-identical after save.
- **WPBakery backend editor** on a throwaway draft page (deleted afterwards): `vc.app` initialises,
  98 mapped elements, Add-Element panel lists 152 items, adding a Text Block creates row/column/text
  shortcodes and opens the "Text Block Settings" modal with TinyMCE (3 editors). Zero console errors
  on WP 7.1 / jQuery UI 1.14.2.
- **Guide editor** (post 13904, Gutenberg, iframed canvas): 198 blocks, all `isValid`, not dirty.
  Console: WP 7.1 warnings that `fbhi-guides-editor-css`, TEC and core global-styles stylesheets were
  "added to the iframe incorrectly" (WP now warns when `enqueue_block_editor_assets` styles get
  copied into the iframe) + AIOSEO SpellChecker "Dictionary not found: sv/sv_SE.aff" (AIOSEO 5.0
  feature, harmless). → Follow-up for the guides module: move the editor-chrome stylesheet handling
  so WP doesn't copy it into the canvas (or accept the warning). Not part of this round.
- **WPForms builder** (form 11048): loads, 12 fields. Three 404s for addon icon PNGs
  (klaviyo, sendgrid, mercado-pago) — pre-existing in 1.9.8.7, cosmetic.
- **Updates screen**: "Du har den senaste versionen av WordPress", 2 plugins pending (the two
  licence-blocked ones).

**Verdict: dev is green.** Nothing in this round needs a child-theme change before prod.

## Log
- 2026-09-15 — dev updates applied + verified (see "Dev results"). Awaiting Daniel's manual check.
- 2026-09-15 — guides follow-ups done in the child theme (editor CSS split, editor canvas styling, Media & Text,
  unpublished-page handling; see docs/guides/README.md 2026-09-15). Deployed to dev with `./upload.sh d`
  and verified (editor canvas, Media & Text on chapter 5, draft/unpublished-root behaviour for visitors vs
  editors). Note: from Claude's shell the deploy needs `SSH_AUTH_SOCK=~/.ssh/agent.sock` (forwarded agent);
  the on-disk key alone is rejected.

- 2026-09-15 — inventory, compatibility research, dev baseline. Prod MCP unreachable
  ("Connection Failed"); prod inventory pending.
- 2026-09-15 (evening) — prod done (see "Prod log"); Daniel finished WPForms + Novamira Pro by hand. Round closed,
  folder moved to `docs/archive/updates-2026-09/`.
