# Update round 2026-10-20

Status: **closed, green.** Daniel took a full prod backup first. WordPress was already current (7.1.3), Salient
has no licence (stays 15.0.9). All plugins updated on dev first (for the WPML major jump), then on prod, the same
day. No emails were sent or triggered (Daniel's rule): no form submits, no test mails; MailPoet's only newsletter
send task (5310, paused since 2026-03-19) stayed paused.

## Plugins (prod)

| Batch | Plugin | Before | After |
| --- | --- | --- | --- |
| A (inactive) | TablePress | 3.3.4 | 3.4.1 |
| A (inactive) | WP Migrate Lite | 2.7.11 | 2.7.12 |
| B | All in One SEO | 5.0.2 | 5.0.3 |
| B | Autoptimize | 3.1.15.1 | 3.1.16 |
| B | Complianz | 7.5.5 | 7.5.6 |
| B | FluentSMTP | 2.4.0 | 2.4.1 |
| B | MapPress | 2.97.12 | 2.97.14 |
| B | WPForms | 2.0.2.1 | 2.0.2.2 |
| C | WPML Multilingual CMS | 4.9.7 | **5.1.0** |
| C | WPML String Translation | 3.5.4 | **5.1.0** |
| D | The Events Calendar | 6.17.5 | 6.18.2 |
| D | MailPoet | 5.39.0 | 5.42.0 |
| D | MailPoet Premium | 5.39.0 | 5.42.0 (offered only after MailPoet, as before) |

Not touched: Novamira / Novamira Pro (never via MCP; prod had no update offered). Dev-only plugins (Jetpack Boost,
WP Mail SMTP, WP Super Cache) left as they were; on dev WPForms and MapPress cannot update (licence), MailPoet is
inactive on dev (as in earlier rounds).

## How

`novamira/execute-php` with `Plugin_Upgrader::bulk_upgrade` per batch, `wp_update_plugins()` first. Batch B on
prod returned an **Nginx 504** while PHP finished; a read-only check showed all 8 done, no `.maintenance`, empty
`wp-content/upgrade`. Afterwards `rt_nginx_helper_purge_all` (usual unlink warnings on the cache folders, 0 cache
files left) + `wp_cache_flush()`.

**WPML 5** (released 2026-09-13, 5.1.0 on 2026-09-29): after the update it shows "WPML is finalizing the database
update — it completes automatically as you use the admin (2 steps left)"; it finished after three admin page loads
(dev and prod). Visible for editors: the old Languages screen now redirects to a combined Settings screen
(`admin.php?page=tm/menu/settings&section=languages`); a "automatic translation pricing has been upgraded" notice.
WPML 5 adds 8 tables (26 → 34 `icl_*`). Fingerprint before/after on both sites identical: default `en`, languages
`en,sv`, `icl_translations` (prod 2057), completed string translations (871), front page 6998 ↔ 9798, menus
Main (en) 21 / Main (swedish) 25, `guide` and `network-project` translatable; registered strings 15034 → 15035
(WPML auto-registers strings as pages are viewed).

## Regression (prod, logged-out visitor, 1400px)

- 8 pages (home en/sv, network-project, kommuner, calendar, FAQ sv, seminar page, IWG form page): all 200, no PHP
  errors, right `lang`; console empty on all; full-page screenshots **pixel-identical** before/after.
- HTML diffs: version strings, WPForms time token, TEC view UUIDs, stylesheet order, and **AIOSEO 5.0.3's
  automatic meta description on the Swedish home page** (before: "Seminarium: Att mäta effekten av FINGER Se vårt
  senaste seminarium …", a run of headlines; after: "Livsstilen har betydelse för att förebygga kognitiv svikt …",
  the page's opening text). Same on dev. Harmless; set a manual description in AIOSEO if a specific text is wanted.
- IWG form 14741: 25 fields, `novalidate`, jQuery validator attached, `wpforms_settings` present (not submitted).
- Counts unchanged: 23 forms / 3240 entries, 5 events, 43 network-projects, 9 kommuner, 764 MailPoet subscribers,
  25 active plugins; MailPoet DB version 5.42.0; Autoptimize `js/jquery/jquery.min.js` exclude still set.

Captures: `~/fbhi-baselines/{dev,prod}-baseline-2026-10-20/` (urls.txt, html, html-after; prod also screens,
screens-after). Dev regression: 8 pages 200, WPML fingerprint identical, calendar clean for visitors, language
switcher SV/EN correct; logged-in console shows "AIOSEO translations couldn't be loaded" (admin bar only).
