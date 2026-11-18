# MailPoet footer links: "Avsluta prenumeration" / "Ändra mejladress" (2026-11)

**Status: INVESTIGATING (2026-11-18). No changes made. Waiting for Daniel's go-ahead before implementing.**

## Annika's request (2026-11-18)

> kan du se över funktionerna "Avsluta prenumeration" och "Ändra mejladress" i nyhetsbrev/MailPoet så att de blir
> på svenska och att funktionen är som den ska? Ligger i foten av alla mejl från MailPoet.

## Findings so far (dev, read-only via Novamira execute-php)

Dev has **MailPoet 5.45.1 installed but deactivated**, so dev can only show settings and source code, not
behaviour. Prod still needs checking (Novamira on prod needs a Claude Code restart).

- **Site language setup:** WPML default language is **en** (sv is secondary, directory-based: `/sv/`), and
  `get_locale()` is `en_US`.
- **MailPoet pages:** every subscription page setting (`manage`, `unsubscribe`, `confirm_unsubscribe`,
  `confirmation`) points to **one page, 10860** ("MailPoet Page", slug `subscriptions`, post type `mailpoet_page`,
  content `[mailpoet_page]`). There's also a captcha page, 11985. WPML doesn't translate `mailpoet_page`
  (`custom_posts_sync_option` = 0), and the pages have no language.
- **How the links are built** (`lib/Subscription/SubscriptionUrlFactory.php`): `getPermalinkOrHome(page)` plus
  `?mailpoet_router&endpoint=subscription&action=manage|unsubscribe|confirm_unsubscribe&data=…`. The permalink has
  no `/sv/`, so WPML serves the page in the **default language, English**. All texts on the page come from
  MailPoet's PHP (`__()`, domain `mailpoet`), not from the page content. So **the pages are English for everyone**,
  even with a Swedish newsletter.
- **Swedish translation is incomplete** (`languages/plugins/mailpoet-sv_SE.po`, revision 2026-09-02). Some strings are
  translated: "Manage your subscription" → "Hantera din prenumeration", "Confirm you want to unsubscribe" →
  "Bekräfta att du vill avregistrera", "You are now unsubscribed." → "Du har nu avslutat prenumerationen.",
  "Unsubscribe" → "Avsluta prenumeration", "Your lists" → "Dina listor", "Accidentally unsubscribed?". Others
  are **missing**: "Save changes", "Email subscription status", the status description, the lists description,
  and the email-change notice. So switching the page to sv alone would leave a mix of Swedish and English.
- **"Ändra mejladress" doesn't work the way the label suggests.** MailPoet's manage page has **no way to
  change the email address**. The field is read-only and the form says
  *"Need to change your email address? Unsubscribe using the form below, then simply sign up again."*
  (`ManageSubscriptionFormRenderer.php:115`). The footer link most likely goes to `[link:subscription_manage_url]`
  (to be confirmed in the prod newsletter/template HTML).

## Still to check on prod (read-only)

1. MailPoet version, and whether the sv_SE language pack is the same as dev.
2. The actual footer HTML in recent newsletters and templates: link texts and the shortcodes behind them
   (`[link:subscription_unsubscribe_url]`, `[link:subscription_manage_url]`).
3. Whether the unsubscribe link goes straight to unsubscribe or to the "confirm unsubscribe" step, and what the
   manage page shows (run a preview URL with `data` = preview, or Annika's own test link).
4. The settings for the subscription pages and the "unsubscribe reason" options.
5. Whether the Nginx cache serves `mailpoet_router` URLs (it shouldn't, since they're query-string, per-subscriber).

## Fix options (draft, refine after prod check)

- **A. Swedish pages:** a child-theme snippet that switches WPML language to sv (and the locale to sv_SE)
  for `mailpoet_router` / `mailpoet_page` requests. Alternatively, a filter on the page permalink so it gets
  `/sv/`. The hook still needs to be chosen.
- **B. Fill the translation gaps:** a `gettext` filter (domain `mailpoet`) in the child theme for the missing
  strings, or WPML String Translation for the `mailpoet` domain (that lives in the DB, outside git, so the child
  theme is preferred).
- **C. Footer wording (Annika, in the MailPoet editor):** rename "Ändra mejladress" to "Hantera prenumeration".
  Changing the address isn't possible: the subscriber has to unsubscribe and sign up again with the new address.
  We could also reword MailPoet's notice in Swedish to explain this clearly.
