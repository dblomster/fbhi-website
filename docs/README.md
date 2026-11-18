# docs

Working notes for the fbhi.se child theme and the WordPress site around it. Live/ongoing things sit at the
top level; finished records move to `archive/`.

## Live / ongoing

| Path | What |
|---|---|
| [update-procedure.md](update-procedure.md) | **Standing checklist for plugin/core update rounds** (dev first, then prod; WPForms only via wp-admin) — each round's record goes in `updates-YYYY-MM-DD/` |
| [updates-2026-11-18/](updates-2026-11-18/README.md) | Latest update round (closed green): AIOSEO, MapPress, Redis Object Cache, TEC, MailPoet, WPForms 2.0.5 |
| [swedish-theme-texts.md](swedish-theme-texts.md) | Salient has no Swedish translation: which theme texts we translate in `functions.php` (search, Post Grid) and how to add more |
| [kommuner-sort-order.md](kommuner-sort-order.md) | Kommuner in Swedish ÅÄÖ order (COLLATE), grid shows all, prev/next labels; dev DB double-encoding |
| [cpt-navigation.md](cpt-navigation.md) | How the blog-like CPTs share the single layout and prev/next nav; how to add another CPT |
| [list-spacing.md](list-spacing.md) | The body-text ul/ol spacing rules in `custom.css` (live on dev + prod), measurements and revert |
| [password-protection.md](password-protection.md) | "Skyddad:" title prefix and protected-excerpt text: how to change/remove them (findings, awaiting Annika); guide CPT follows core's handling |
| [guides/](guides/README.md) | Guides module (`guide` CPT) — built on dev, prod on hold; plan, decisions, content notes |
| [forum/](forum/README.md) | Closed forum for Annika's regional/municipal group: FluentCommunity (free) evaluation + demo on dev, Swedish translation and WPML notes |
| [wpbakery-image-captions/](wpbakery-image-captions/README.md) | Caption field for Salient's Image With Animation in the guide caption style — demo on dev (13972 sv / 13974 en), awaiting decision; revert steps |
| [page-content/](page-content/README.md) | Git-tracked reference copies of WP-managed page bodies (WPBakery shortcodes) |
| [wpforms-corrupted-post-data/](wpforms-corrupted-post-data/README.md) | WPForms "corrupted post data" / tick-every-box — Phase 1 done, Phase 2 monitoring through the 30 Sep 2026 seminar |
| [mailpoet-unsubscribe-manage/](mailpoet-unsubscribe-manage/README.md) | MailPoet footer "Avsluta prenumeration" / "Ändra mejladress": pages render in English, can't change email — investigating, awaiting go-ahead |
| [mailpoet-subscriber-fields/how-to-import.md](mailpoet-subscriber-fields/how-to-import.md) | Routine: WPForms export → MailPoet import with subscriber fields (Annika) |

## Archive (closed)

| Path | What |
|---|---|
| [archive/updates-2026-08/](archive/updates-2026-08/README.md) | Update round Aug 2026 |
| [archive/updates-2026-09/](archive/updates-2026-09/README.md) | Update round Sep 2026 (dev + prod on WP 7.1); prod-via-Novamira batch procedure and quirks |
| [archive/mailpoet-subscriber-fields-2026-09/](archive/mailpoet-subscriber-fields-2026-09/2026-09-plan.md) | Setting up MailPoet custom fields from WPForms exports — plan and execution record, closed 2026-09-18 |
| [archive/mailpoet-blocked-lists-2026-09/](archive/mailpoet-blocked-lists-2026-09/2026-09-investigation.md) | Why two MailPoet lists refused to trash (stuck never-sent newsletters) and the fix, closed 2026-09-18 |
| [archive/mailpoet-phone-map-link-2026-11/](archive/mailpoet-phone-map-link-2026-11/README.md) | Newsletter words turned into a map link on phones: the phone's mail app (data detectors) does it, not MailPoet; closed 2026-11-18 by replying to Annika |
