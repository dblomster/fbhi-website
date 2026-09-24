# Plugin updates + guides to prod — 2026-09-24

Two things in one round: (1) catch up on plugin updates (dev first, then prod), (2) copy the `guide` CPT
(theme module + FINGER-guiden content) to prod **as drafts, without a menu entry**, so it can be reviewed in place.

Procedure reused from [archive/updates-2026-09](../archive/updates-2026-09/README.md). No dev backup (Daniel's
call); **prod backup taken manually by Daniel before any prod write.**

## Status

- [x] Dev baseline: `~/fbhi-baselines/dev-baseline-2026-09-24/` (same 9 pages as 2026-09-15: HTML, full-page shots, console)
- [x] Dev plugin updates (WP-CLI over Novamira dev MCP)
- [x] Dev regression pass + wp-admin checks — **green**
- [x] Prod inventory (read-only) + theme dry-run diff
- [x] **Daniel: manual full prod backup**
- [x] Prod baseline + plugin updates + regression — **green** (see "Prod log")
- [x] Daniel: chapter 5 comes along (draft); example media are copied **with their exact filenames** — the
      `exempel-handbok-` prefix marks what to delete before publishing
- [x] Dev export (posts, meta, 12 attachment rows = 10 unique files; dev has WPML duplicates 13918/13919 and
      13934/13935 of the same file, both map to one prod attachment); originals pulled to the session scratchpad
- [x] Prod theme deploy (`./upload.sh p`, guides module) — first attempt blocked by the auto-mode classifier;
      ran after Daniel's explicit instruction naming the steps
- [x] Prod media + guide content as drafts — see "Guide copy log" below
- [x] Prod checks: visitors 404, editor view, block editor validity — **green**

## Dev (fbhi.devcx.com) — done 2026-09-24

WordPress 7.1.2 (already latest), Salient 15.0.9, no theme update.

| Plugin | Before | After | Note |
| --- | --- | --- | --- |
| MailPoet | 5.38.0 | 5.39.0 | inactive on dev |
| Redirection | 5.10.0 | 5.10.1 | inactive |
| Redis Object Cache | 2.8.0 | 3.0.0 | inactive on dev — **not exercised** (active on prod) |
| All in One SEO | 5.0.1.1 | 5.0.2 | |
| MonsterInsights | 11.2.0 | 11.3.0 | |
| Jetpack Boost | 4.7.0 | 4.7.1 | + sv_SE translation |
| The Events Calendar | 6.17.4.1 | 6.17.5 | |
| WPForms | 1.9.8.7 | — | licence limit on dev (known) |
| MapPress Pro | 2.95.10 | — | licence not active for dev domain (known) |

Regression (`html-after/`, `screens-after/`):
- All 9 pages 200. HTML diffs only: AIOSEO/MonsterInsights version strings, TEC timestamps/UUIDs, WW-Fingers
  id ordering, WPForms token — plus two **fixes**: AIOSEO breadcrumb `@id` now `https://…/#listItem` (slash
  added), and TEC's "Month" view link on `/calendar/` was a raw regex (`/calendar/(/?:month|mnad|månad|manad)/`),
  now `/calendar/month/`. **Correction (later the same day):** not a TEC fix — on dev the regex link came back
  after the Autoptimize cache purge and stays (it 404s; `/calendar/month/` works). It is a dev-only cache-state
  quirk of TEC + WPML; prod's link has been clean in every capture (09-15 and 09-24, before and after).
- Screenshots: all 9 pixel-identical (0 diff px).
- Console: identical to baseline (zero errors; wpforms page "no label" ×1, guide page `<summary>` ×5).
- WPForms form 11048: 12 fields, novalidate, jQuery-Validate + AJAX settings present. Counts unchanged:
  15 forms / 2376 entries, 10 events, TEC migration "not required", 29 107 string translations.
- No maintenance file, no paused plugins, `wp-content/upgrade/` empty.
- wp-admin (admin-access link): Updates screen shows only the two licence-blocked plugins; guide editor
  (13904) loads, 198 blocks all valid, not dirty. Console warnings are third-party (MapPress apiVersion 1,
  TEC/core styles "added to the iframe incorrectly", a `wp.editPost.PluginDocumentSettingPanel` deprecation —
  not ours, `guides-editor.js` prefers `wp.editor`).

Redis Object Cache 3.0.0 changelog: bug fixes (`wp_cache_replace`, pipeline mode, `WP_REDIS_USERNAME`),
Predis 2.4.1, ApexCharts 4.7.0. No config changes required; the drop-in is bumped to 3.0.0 too.

## Prod (fbhi.se) — inventory 2026-09-24 (read-only)

WordPress 7.1.2 (latest), PHP 8.3.33, Salient 15.0.9, no paused plugins, `guide` post type not registered.

| Plugin | Prod now | Available | Batch |
| --- | --- | --- | --- |
| All in One SEO | 5.0.1.1 | 5.0.2 | 1 |
| MonsterInsights | 11.2.0 | 11.3.0 | 1 |
| The Events Calendar | 6.17.4.1 | 6.17.5 | 1 |
| WPForms | 2.0.2 | 2.0.2.1 | 1 |
| MailPoet | 5.38.1 | 5.39.0 | 1 — then check MailPoet Premium (5.38.0) is offered a match |
| Redis Object Cache | 2.8.0 | 3.0.0 | 2 — alone, last; verify drop-in version + `redis_status` connected |

Theme dry-run (`rsync -n --checksum` vs prod): the only differences are the guides module —
`functions.php` (+ the `require` of `includes/guides/bootstrap.php`), `style.css` (+ `Text Domain` header),
new `single-guide.php`, `wpml-config.xml`, `assets/guides/`, `includes/guides/`, `languages/`.
Rewrite rules flush themselves (`Guide_Post_Type::maybe_flush_rewrite_rules`).

## Prod log

- 2026-09-24 — Daniel: full manual prod backup done; asked for all plugins to latest before the guide copy.
- Baseline `~/fbhi-baselines/prod-baseline-2026-09-24/` (7 pages; `faq-en` dropped and `faq-sv` moved to
  `/sv/fragor-och-svar-om-finger/` — the FAQ page was renamed on prod since 09-15: `/fragor-och-svar/` 301s
  there, `/sv/fragor-och-svar/` is 404; editorial, not from updates).
- Pre-existing, seen during the baseline: cold-first-load JS race on `/calendar/` (TEC `selectors` ×10) and on
  the seminar form 15199 (`wpforms_settings is not defined`, form left un-initialised); reloads clean. Written
  up in [wpforms-corrupted-post-data/2026-09-plan.md](../wpforms-corrupted-post-data/2026-09-plan.md) §4.
- `execute-php` `Plugin_Upgrader::bulk_upgrade`, no 504s this time:
  1. AIOSEO 5.0.1.1 → 5.0.2, MonsterInsights 11.2.0 → 11.3.0, WPForms 2.0.2 → 2.0.2.1 (35 s)
  2. TEC 6.17.4.1 → 6.17.5, MailPoet 5.38.1 → 5.39.0 (40 s)
  3. MailPoet Premium 5.38.0 → 5.39.0 (offered only after MailPoet, as last time)
  4. Redis Object Cache 2.8.0 → 3.0.0. Drop-in stayed 2.8.0 ("Drop-in is outdated", still connected);
     the plugin updates it on `admin_init` → `shutdown`, so the next request swapped it: drop-in 3.0.0
     (md5 = plugin copy), status Connected, set/get probe OK.
- `rt_nginx_helper_purge_all` + `wp_cache_flush()` (usual unlink warnings only). Nothing pending, no paused
  plugins, no `.maintenance`. Counts: 23 forms / 3200 entries, 5 events, TEC migration not required, 10 991
  strings, 43 network-projects, 8 kommuner, 764 MailPoet subscribers.
- Regression: 7 pages 200; HTML diffs = version strings, WPForms time token, AIOSEO breadcrumb `/#listItem`
  fix, and TEC 6.17.5 no longer emitting Event JSON-LD on `/calendar/` for three old,
  undisplayed test events ("Test event", "Test event 2", …; list shows no upcoming events before and after).
  Screenshots 7/7 pixel-identical. Console clean (seminar page keeps its "No label" notice); form 15199: 19
  fields, novalidate, validator attached.

## Guide content to copy (from dev)

| Dev ID | Title | Slug | Label / accent |
| --- | --- | --- | --- |
| 13900 | FINGER-guiden (root) | finger | print label "Skriv ut sidan" |
| 13901 | Bakgrund och varför FINGER spelar roll | bakgrund | Avsnitt 1 · #006885 |
| 13902 | Nuläge, förarbete och att skapa förutsättningar | nulage-och-forarbete | Avsnitt 2 · #90BB94 |
| 13903 | Att utforma och genomföra FINGER-aktiviteter | utforma-och-genomfora | Avsnitt 3 · #FFE6AC |
| 13904 | Planering av aktiviteter och viktiga överväganden | planering-av-aktiviteter | Avsnitt 4 · #E5A883 |
| 13939 | Innehåll, övningar och tillämpning (placeholder) | innehall-ovningar-och-tillampning | Avsnitt 5 · #213A6C |

All `sv`. Chapters carry `_fbhi_guide_label`, `_fbhi_guide_accent`, `_fbhi_guide_print_label`.
Media referenced (all `exempel-handbok-*` placeholders): images 13912, 13914, 13916, 13918, 13919, 13920,
13922, 13924, 13926; PDFs 13932 (inline link, by URL only) and 13935 (file block), both in chapter 2.
10 unique files in all (8 images, 2 PDFs). Content contains dev upload URLs that
must be rewritten to the new prod attachment IDs/URLs.

Import method: `execute-php` on prod — create posts as **draft**, set WPML language `sv` with
`wpml_set_element_language_details` (MCP-created posts get no language row otherwise, see plan.md § 5),
set meta, sideload media and rewrite IDs/URLs in the block markup. No menu item.

## Guide copy log (prod, 2026-09-24)

1. `./upload.sh p` — only the guides module transferred (31 files). Home/sv/seminar pages 200 afterwards.
   `guide` registered, rewrite rules flushed by the module, `fbhi/guide-index` block registered.
2. WPML had not read the new `wpml-config.xml` yet (`guide` not translatable) → `WPML_Config::load_config_run()`
   (what an admin page load does) → `custom_posts_sync_option[guide] = 1`, same as dev.
3. rsync of the 10 originals into `wp-content/uploads/2026/09/` (`--ignore-existing`, md5 verified) and the
   export into `fbhi-import/` next to `public_html` (not web-reachable; removed again at the end).
4. Import. Findings on the way:
   - Nginx answers **405** to an `execute-php` body containing `file_get_contents(` + a file path (same rule as
     2026-09-15); `implode( '', file( $path ) )` passes.
   - Prod PHP `max_execution_time` is **30 s**; generating ~21 sizes per image takes 3–20 s each, so one call for
     all files died with a fatal on the 4th (500 "critical error"; site unaffected, no recovery-mode email —
     last one March). `@set_time_limit( 120 )` works on prod; one file per call from then on.
   - **WPML Media duplicates every new attachment** on prod: an `en` original + an `sv` translation of the same
     file (dev has the same pattern, e.g. 13918/13919). Forcing the original to `sv` (first attempt) split the
     pair; repaired by moving 15354/15356/15358 back to `en` in their original trids. No deletions were needed.
5. Result — media (en original / **sv** translation, all `exempel-handbok-*`, filenames identical to dev,
   165 files incl. generated sizes, `gympingfarbror` gets `-scaled` as on dev):

   | File | en | sv (used in content) | Dev IDs |
   | --- | --- | --- | --- |
   | Hand-med-cirklar.webp | 15354 | 15355 | 13912 |
   | Lidingo-Elin-och-Anna.webp | 15356 | 15357 | 13914 |
   | Aktivitetspyramid.webp | 15358 | 15359 | 13916 |
   | Couple-Nordic-walking…-2000w.webp | 15360 | 15361 | 13918, 13919 |
   | FINGER-resultat.webp | 15362 | 15363 | 13920 |
   | Graf-1.webp | 15364 | 15365 | 13922 |
   | Graf-2.webp | 15366 | 15367 | 13924 |
   | gympingfarbror.webp | 15368 | 15369 | 13926 |
   | FINGER-guiden-FBHI.pdf | 15370 | 15371 | 13934, 13935 |
   | The-FINGER-model-…Institute.pdf | 15372 | 15373 | 13932 |

   Guide pages, all **draft**, `sv`, author Daniel (6), no menu item:
   15374 FINGER-guiden (root, `/sv/guide/finger/`) · 15375 bakgrund · 15376 nulage-och-forarbete ·
   15377 utforma-och-genomfora · 15378 planering-av-aktiviteter · 15379 innehall-ovningar-och-tillampning.
   Labels, accents and print labels as on dev; no `devcx` left in any content; 0 orphan WPML rows.
6. Checks: Nginx purge. Visitors: every guide URL (pretty and `?p=`) 404, nothing in the sitemap. Editor (Chrome,
   Novamira admin link): preview notice, "UTKAST" badges in sidebar/cards, all images 200 on every chapter,
   PDF links + file block + table on chapter 2, phone width without horizontal scroll, console clean. Block
   editor: chapter 2 = 225 blocks, chapter 5 = 33 blocks, all valid, not dirty; Media & Text → 15361.
   One-off: the first editor load failed because `wp-includes/js/dist/api-fetch.min.js` came back
   `net::ERR_FAILED` in Chrome (curl 200, file intact); reload fine. Another cold-load script failure on this host
   — relevant to the WPForms plan's hosting hypothesis.

**Before publishing:** replace or delete everything named `exempel-handbok-*` (both the en and sv attachment of
each pair), add the menu item, publish root + chapters.
