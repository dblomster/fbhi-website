# Closed forum: FluentCommunity evaluation (dev)

Started 2026-11-17. Annika asked if there is a forum plugin for a **closed forum for one group of people from
different regions and municipalities**, possibly more groups later. Requirements are vague, so the plan is a
demo on dev that she can try, and we suggest **Pro only if a Pro feature is actually needed**.

## State on dev (fbhi.devcx.com)

- **FluentCommunity 2.14.0 (free)** installed + active on dev only. Wizard: name "FBHI Forum", slug `forum`,
  template *Blank*; FluentCRM/FluentCart install, vendor newsletter and telemetry all **unticked**.
- Portal access: **Only logged-in users** (set in Portal Settings → General; the wizard's choice did not stick).
  Open registration stays **off**; members are added by an admin.
- Rewrite rules had to be flushed (`wp rewrite flush`) after the slug change, or `/forum/` redirect-guessed to the
  event "Forum för jämlik hälsa 2026".
- Email: dev's FluentSMTP has **simulate emails = yes**, so nothing leaves dev.
- Demo content (fictional people and organisations, all `@example.com`, user meta
  `fbhi_demo_user = fluent-community-demo-2026-11`, WP role subscriber, locale sv_SE):
  - users 8–14: demo-karin, demo-erik, demo-sara, demo-maria, **demo-medlem**, demo-johan (14, Johan Andersson,
    Sjöby kommun; replaced demo-ahmed, ID 11, whom Daniel deleted as a test, see "Removing a member" — orphans
    cleaned and post counters recounted afterwards, poll and comments re-created under Johan) (test login to see the
    forum as an ordinary member; password given to Daniel out of band, not stored here)
  - space group 1 "Regioner och kommuner": *Diskussion*, *Möten och aktiviteter*, *Tips och material* (all
    **secret** = invite-only, invisible to non-members); Karin is space moderator in the first two
  - space group 2 "Fler grupper (exempel)": *Exempel: Arbetsgrupp* (**private**, join requests; demo-medlem is not
    a member, to show the request flow), *Exempel: Öppen för alla medlemmar* (**public** within the login wall)
  - 8 posts with comments, replies, likes and a poll, back-dated over the last 20 days
- Swedish URL: **https://fbhi.devcx.com/sv/forum/** (see WPML below). Logged-out visitors get the portal's own
  login page (`/sv/forum/login`, email + password).
- Forum admins: FluentCommunity treats every WP user with `manage_options` (filter
  `fluent_community/super_admin_capability`) as **forum super-admin**, so all WP administrators can read and manage
  every space, secret ones included. On dev that is Annika, Daniel, Emelie, infobahn.fbhi and provideit. Worth
  telling Annika before go-live: the group's "closed" content is visible to every site administrator.
- Annika's access: demo-medlem (member view) + her existing dev admin account (= forum admin). Reply drafted in
  `incoming/2026-11-17-svar-annika-forum.md` (how to add members in free, Pro features incl. email invitations /
  CSV import, six requirement questions); **SENT by Daniel 2026-11-17 in a modified version. Waiting for her
  feedback** before translating or doing anything else.

Cleanup when done: deactivate + delete the plugin (its tables are `wp_fbhi__1_fcom_*`, option
`fluent_community_settings`), delete users with meta `fbhi_demo_user`.

Seeding gotcha: when calling FluentCommunity routes with `rest_do_request()` several times in one PHP run, the
framework's `Request` singleton **keeps earlier params** (array_merge), so e.g. a poll's `survey` leaked into later
posts and `parent_id` into later comments. Pass every key explicitly (`survey => []`, `parent_id => 0`, …).

## Adding a member (free)

1. wp-admin → Användare → Lägg till användare: email, name, role **Prenumerant**, language Svenska, tick "send the
   new user an email" (WP's set-password email; on prod this really sends).
2. Forum → the space → Members → **Add Member** → tab *WP Users* → search the person → role Member / Moderator /
   Admin → Add as member. Repeat per space (or tab *From Space/Course* to copy a whole member list).

Pro-only in that panel: **Invitations** (email invite; the invitee creates their own account even with
registration off) and **CSV import** (Name, Email, Username).

## Removing a member

Tested 2026-11-17 by deleting demo-ahmed (ID 11) in wp-admin. FluentCommunity's `deleted_user` handler
(`CleanupHandler::handleUserDeleted`) wipes **all their forum content**: posts, comments, likes, notifications,
space memberships, profile; media is flagged for cron deletion. WP's "attribute all content to …" choice is
**ignored** for forum content. Side effects (raw SQL deletes): other members' comments on the deleted posts stay in
the DB as invisible orphans (here comment 11 on post 11), and counters on remaining posts are not recalculated
(post 13 still says 2 comments, has 1).

To keep the discussions when someone leaves, don't delete the WP user: remove them from the spaces (Members →
remove), or set their forum status to **blocked** (free; `PATCH members/{id}` with `status`, in the member admin
UI), which keeps their posts but locks them out.

## Free vs Pro (as seen in the free admin, 2026-11-17)

Free covers what the brief needs so far: login-only portal, unlimited spaces and space groups, public / private /
secret spaces, admin-added members and join requests, **space-level moderator/admin roles**, posts, comments,
replies, reactions, polls, mentions, bookmarks, member directory, profiles, email notifications (new comment,
reply, admin announcement email) and a weekly digest, privacy settings (who sees the member list/profiles),
dark mode, menu/sidebar links, courses.

Pro-locked in the free UI: **Managers** (community-wide moderators), **Content moderation** (reporting, review
queue), **Topics**, custom CSS/JS snippets (we can do CSS in the child theme anyway), members grid view,
sidebar collapse, "Modern" media layout, members changing their own username/email, **auto-login from email
links**, sitemap, FluentCRM tag-based access, leaderboard/badges, document library, cloud storage.
Pro price (fluentcommunity.co/pricing, Black Friday promo shown 2026-11-17): single site $119/yr or $279 lifetime.

## Swedish translation

- Text domain `fluent-community`; **all portal UI strings go through PHP `__()`** (`app/Services/TransStrings.php`,
  ~2,600 strings, handed to the Vue app as `i18n`), so a normal `.mo` / `.l10n.php` for `sv_SE` translates the
  whole portal. About 3,400–4,000 strings in total, ~14k words.
- translate.wordpress.org sv: **4 %** (113 strings), last activity ~2 years ago; no language pack is built below
  90 %, so today there is no Swedish file at all.
- Where to keep our translation so plugin updates don't wipe it:
  - **Recommended:** `.po` + compiled file in the **child theme** (git-tracked, deployed by `upload.sh`), loaded via
    the `load_textdomain_mofile` / `load_translation_file` filters for domain `fluent-community` + `sv_SE`. Not
    touched by plugin updates **or** by an official language pack if sv ever reaches 90 %.
  - Alternative: Loco Translate (saves to `wp-content/languages/loco/plugins/`, editable in wp-admin, can use a
    DeepL key) — survives updates too, but lives outside git.
  - Not `wp-content/languages/plugins/` (an official pack would overwrite it) and never inside the plugin folder.
- Each update round: regenerate the POT from the new version, merge, translate new strings (until then they show
  in English).
- **Decided 2026-11-17 (Daniel):** child theme, in git. **Translation itself waits for Annika's feedback** on the
  English demo. Starting point for the draft: the wordpress.org sv export
  (`translate.wordpress.org/projects/wp-plugins/fluent-community/stable/sv/default/export-translations/?format=po`).

## WPML

- FluentCommunity has **no WPML/multilingual code** at all. UI language simply follows the WP locale, and WPML
  sets that from the URL: `/forum/` = English (site default language is `en`), **`/sv/forum/` = Swedish**
  (verified: `lang="sv-SE"`, portal base URL and REST URL both stay under `/sv/`).
- Content (spaces, posts, comments) is **not** per language: everyone sees the same posts in both UIs. Fine for a
  forum; space names/descriptions are single-language.
- No language switcher inside the portal (it does not use the Salient header); can be added as sidebar links
  (Menu Settings) to `/forum/` and `/sv/forum/` if English is wanted later.
- **Emails** are built outside a page request (cron), so they would use the site locale (en_US) and links to
  `/forum/`. Fix in the child theme when going live: `fluent_community/base_url` filter → `/sv/forum`, and switch
  to `sv_SE` for FluentCommunity emails. Per-user language in emails (for future English members) would need
  custom code.
