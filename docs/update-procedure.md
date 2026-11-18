# Update procedure (plugins, core) — dev first, then prod

The standing checklist for update rounds. It condenses what the rounds in `updates-2026-*/` and `archive/` actually
did. Each round still gets its own record folder (`docs/updates-YYYY-MM-DD/README.md`) with before/after tables and
anything new. Last revised 2026-11-18.

## Ground rules

- **Daniel takes a full prod backup before any prod update.** Dev can start while the backup runs.
- **Never trigger emails on prod**: no form submits, no test mails, no MailPoet sends.
- **Novamira on prod** is enabled per session by Daniel. If the `novamira-fbhi-prod` tools are missing, ask him
  to enable it and run `/mcp` (or restart Claude Code). Prod admin access links need his OK.
- **Never update Novamira / Novamira Pro via the MCP** (it severs the connection). Daniel does those by hand.
- Salient has no licence and stays on 15.0.9.

## 1. Find out what's on offer — in TWO places

1. `novamira/execute-php`: `delete_site_transient('update_plugins'); wp_update_plugins(); wp_version_check([], true);`
   then list `get_site_transient('update_plugins')->response` with installed versions and active state.
2. **wp-admin → Dashboard → Updates** (`update-core.php?force-check=1`, via a `create-admin-access-link` session in
   Chrome). **WPForms (Pro/Basic licence) only adds its offer in wp-admin.** The execute-php check (REST context)
   missed WPForms 2.0.5 on 2026-11-18.

Prod-only offers: WPForms and MapPress (dev licences don't cover them), MailPoet Premium (offered only after MailPoet
itself is updated). WordPress minor releases **auto-update on prod**, so prod is usually already on the latest minor.

## 2. Pre-checks (read-only)

- **MailPoet**: no sending task due:
  `SELECT … FROM {prefix}mailpoet_scheduled_tasks WHERE type='sending' AND status IS NULL AND deleted_at IS NULL` → 0.
  (Task 2437 `beamer` with status NULL is harmless, it's MailPoet's in-app news fetch.)
- No `.maintenance`, `wp-content/upgrade*` empty.
- **Baseline counts**: forms + entries, events, network-projects, kommuner, guides, MailPoet subscribers,
  `icl_translations`, `icl_strings`, number of `icl_*` tables, WPML default language (`en`), front page (6998),
  active plugins, Autoptimize `autoptimize_js_exclude` still contains `js/jquery/jquery.min.js`. On prod also: Redis
  connected, `WP_REDIS_GRACEFUL` true, drop-in version.
- **Baseline HTML** of the 8 regression pages into `~/fbhi-baselines/{dev,prod}-baseline-YYYY-MM-DD/html/` (copy
  `urls.txt` from the previous round; on prod add a `?nocache=$RANDOM` query to get `x-cache: BYPASS`).

## 3. Run the batches (dev first)

`novamira/execute-php` with `Plugin_Upgrader( new Automatic_Upgrader_Skin() )->bulk_upgrade( [...] )`. Before
**every** batch run `delete_site_transient('update_plugins'); wp_update_plugins();`, or the upgrader may return `true`
without doing anything. Prefix all PHP variables (wrapper quirk); add `@set_time_limit(180)` on prod.

| Batch | What |
| --- | --- |
| A | Plugins inactive on dev (MailPoet, Redis Object Cache) — dev only, as a rehearsal |
| B | Low-risk plugins (ACF, AIOSEO, Autoptimize, Complianz, FluentSMTP, MapPress, FluentCommunity on dev) |
| R | Redis Object Cache on its own (prod). The drop-in `wp-content/object-cache.php` is **not** replaced by the upgrade; it's copied on the next wp-admin page load. Verify its version after an admin load. |
| C | WPML Multilingual CMS, then String Translation in a separate call |
| D | The Events Calendar + MailPoet, then MailPoet Premium if it's offered afterwards |
| W | **WPForms on prod: update in wp-admin** (Dashboard → Updates, tick WPForms, "Update Plugins"), since that's where its offer appears. Can't be rehearsed on dev. Read the changelog first ("View version details"). |
| Core | Usually already auto-updated on prod. On dev: `Core_Upgrader( new Automatic_Upgrader_Skin() )->upgrade( find_core_update( 'x.y.z', 'en_US' ) )`. Prod: never put `file_get_contents(` + a path in a request body (Nginx 405 rule). |

Leave dev-only plugins (Jetpack Boost, LiteSpeed Cache, WP Mail SMTP, WP Super Cache) as they are.

**Prod Nginx 504** on the bigger batches is normal: PHP keeps running. Don't rerun; check read-only (versions, no
`.maintenance`, empty upgrade folders, MailPoet `db_version` in `{prefix}mailpoet_settings`).

After the prod batches: `do_action('rt_nginx_helper_purge_all'); wp_cache_flush();` (hundreds of "unlink …
Operation not permitted" warnings are normal; the cache is still refreshed).

## 4. Regression

- After-HTML of the 8 pages into `html-after/`; token-level diff that ignores Salient `fws_…` ids, TEC UUIDs,
  `data-wp-key` ids, cache-busters and base64 blobs. Expect only version strings and Autoptimize `ver` timestamps.
  The first uncached load after a purge can time out once; retry.
- Counts identical to the baseline.
- Chrome as visitor (separate isolated context): console clean on home, calendar, a guide page and the IWG form
  (14741: `novalidate`, jQuery validator attached, `wpforms_settings` present, same real fields; honeypots
  `field_1` / `field_2` move around by design). **Do not submit.**
- Chrome as admin: a guide page in the block editor (dev 13901, prod 15375): all blocks `isValid`, not dirty,
  AIOSEO sidebar present, no console errors. Leave without saving. Also check Dashboard → Updates is empty.
- WPML changes: the "finalizing the database update" notice clears after ~3 admin page loads.

## 5. Record and close

- Fill `docs/updates-YYYY-MM-DD/README.md` (tables per site, how it went, regression, anything new) and update
  this file if the procedure changed.
- Memory note for the round; commit + push after Daniel's OK.
- Remind Daniel to disable Novamira on prod if he wants it off.
