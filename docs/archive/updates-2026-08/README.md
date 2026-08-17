# Plugin update round — 2026-08-17/18

Status of the August 2026 update round on **prod (fbhi.se)**. Full file + DB backup was
taken manually before starting. Updates were applied by hand in wp-admin; verification
was done via the Novamira MCP connection (read-only checks), curl, and Chrome DevTools.

## Baseline (captured before any update)

Stored at `~/fbhi-baselines/baseline-2026-08-17/` (Daniel's WSL home, outside the repo):

- Full-page screenshots (1440x900 viewport) + raw HTML of 8 key pages: home en/sv,
  a network-project single, a kommuner single, `/calendar/`, FAQ en/sv, and the
  Seminarium 18 augusti WPForms page.
- Console baseline: zero JS errors on all pages; two pre-existing a11y notices.
- `MANIFEST.md` in that folder documents URLs, method, and the regression procedure.

## Applied 2026-08-17/18 — all verified, zero regressions

| Plugin | From | To |
| --- | --- | --- |
| TablePress (inactive) | 3.3.1 | 3.3.3 |
| WP Migrate Lite (inactive) | 2.7.7 | 2.7.11 |
| WPML Multilingual CMS | 4.9.4 | 4.9.6 |
| WPML String Translation | 3.5.2 | 3.5.3 |
| Advanced Custom Fields | 6.8.2 | 6.8.7 |
| Complianz | 7.4.6 | 7.5.3 |
| FluentSMTP | 2.2.95 | 2.3.1 |
| Font Awesome | 5.1.5 | 5.2.1 |
| MapPress | 2.96.6 | 2.97.9 |
| MailPoet | 5.30.0 | 5.35.1 |
| MailPoet Premium | 5.30.0 | 5.35.0 |
| The Events Calendar | 6.16.2 | 6.17.2 |
| MonsterInsights | 10.1.3 | 11.1.2 (major) |
| All in One SEO | 4.9.7.2 | 5.0.0.1 (major) |
| WPForms | 1.10.0.5 | 2.0.0.4 (major) |

**WordPress core 6.9.7 → 7.0.4** applied 2026-08-18 (after the dev-parity check below)
and verified: DB migrated (db_version 61833), all 8 baseline pages 200 and
pixel-identical (≤0.02% sampled diff), consoles clean, WPForms JS init intact on the
IWG form, theme/plugin states preserved, no pending updates. PHP 8.3.32.

### Verification results

- **Visual**: all 8 baseline pages pixel-identical post-update (sampled diff ≤0.02%,
  identical dimensions).
- **Console**: no JS errors on any page; only the pre-existing unlabeled-field notice
  on the seminarium form page remains.
- **WPForms 2.0**: all 22 forms and all entries intact; front-end markup unchanged from
  1.10; JS init (novalidate + jQuery-Validate + AJAX submit) verified live.
- **AIOSEO 5.0 output diff vs baseline HTML**: only additive change — pages without a
  manual meta description now get an auto-generated one (+ og:/twitter: variants) built
  from page content. Titles, canonicals, hreflang, JSON-LD types unchanged.
  The auto-descriptions are unedited page text (the EN homepage one inherits a
  "lifestyld" typo from page content) — curating them is an optional content task.
- **Events Calendar 6.17.2**: the `/calendar/` list page no longer emits the aggregate
  Event JSON-LD block (list items still render). Single event pages still emit proper
  `Event` schema, so no practical SEO loss.
- **Caching**: Nginx cache confirmed serving fresh post-update HTML; all referenced
  Autoptimize bundles return 200.

### Operational notes

- **No WP-CLI on the Loopia box** (searched PATH + standard dirs). Updates must go
  through wp-admin or WP's `Plugin_Upgrader`.
- **Nginx Helper `purge_all()` from PHP fails**: every unlink under
  `/var/site/.../tmp/fastcgi/` returns "Operation not permitted" (~1500 errors).
  In practice pages re-cached fresh anyway (short TTL and/or purge-on-update), but
  don't rely on programmatic full purges — verify with a `?cb=` cache-buster instead.
- **WPForms 2.0 update offer temporarily vanished** from the update transient between
  the check and the update (license API hiccup); reappeared/resolved on re-check.

## Deliberately NOT updated

1. **Salient 15.0.9 → 18.2.1** (+ Core 1.9.9 → 3.1.5, + Salient WPBakery 6.9.2 → 8.7.3,
   all three together per ThemeNectar guidance) — **deferred: no update license access
   at the moment.** When it happens: audit the child theme (`header.php` override,
   Salient internals usage) against Salient 18; rehearse on dev first.
2. **WordPress core 6.9.7 → 7.0.4** — initially held back because official Salient
   WP 7.0 support starts at 18.2.0 (themenectar.com/changelogs/salient.html; ThemeForest
   lists WP 6.4.x–7.0.x). **Assessment revised 2026-08-18:** no confirmed reports of
   15.x breaking on WP 7.0; the one documented WP 7.0-specific Salient bug (Delay-JS
   logic, fixed in 18.2.1) is in a feature disabled on prod (`salient_redux`:
   `delay-js-execution = 0`, `defer-javascript = 0`); and Daniel's dev site runs
   Salient 15.0.9 on WP 7.0.4 without problems. → The core update can proceed without
   the Salient update, after a dev-parity check (see below), accepting
   "works-but-officially-unsupported" status until the Salient license is renewed.
   Dev checklist before prod core update — **completed 2026-08-18 on
   fbhi.devcx.com** (dev, via Novamira MCP + browser automation), all green:
   - Dev runs the identical Salient stack on WP 7.0.4: parent 15.0.9,
     salient-child active, Salient Core 1.9.9, Salient WPBakery 6.9.2 (PHP 8.4.23 —
     stricter than prod's 8.3.32).
   - Front-end (home en/sv, calendar) renders clean.
   - WPBakery backend editor fully exercised on a throwaway draft page: builder
     loads, element edit modal + TinyMCE work, edits persist through the save
     pipeline with Salient's param defaults correctly applied. Zero console errors.
   - Salient theme options (Redux, 1292 fields) loads and saves; dynamic CSS
     regenerates (`salient_dynamic_css_success = true`).
   - Zero PHP warnings/notices/deprecations captured across all dev calls.
   → **Green light: prod core 6.9.7 → 7.0.4 may proceed** (same baseline/regression
   routine as the plugin round). **Done + verified 2026-08-18** — see top of this doc.
3. **Novamira / Novamira Pro** — never update/deactivate via the MCP connection itself
   (see CLAUDE.md safety rules); update these manually and deliberately.
