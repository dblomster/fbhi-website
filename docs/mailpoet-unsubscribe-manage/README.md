# MailPoet footer links: "Avsluta prenumeration" / "Ändra mejladress" (2026-11)

**Status: step 1 IMPLEMENTED on dev + prod 2026-11-18** (`salient-child/includes/mailpoet.php`, required from
`functions.php`; deployed with `./upload.sh p`, Daniel's OK). Reply to Annika (item 2 of her email, covering steps 2–3)
**drafted** in `incoming/2026-11-18-svar-annika-mailpoet.md`; Daniel sends, then mark it sent here.

Verified on prod after deploy + purge: `get_permalink(10860)` = `https://fbhi.se/sv/?mailpoet_page=subscriptions`;
`SubscriptionUrlFactory` confirm-unsubscribe / unsubscribe / manage / confirmation URLs all start with
`https://fbhi.se/sv/?mailpoet_page=…`; the confirm page renders `lang="sv-SE"`, "Bekräfta att du vill avregistrera",
"Ja, avregistrera mig"; public pages 200. On dev only the function itself could be checked (MailPoet inactive there):
filter registered at priority 20, `mailpoet_page` → `/sv/`, other posts untouched.

## Annika's request (2026-11-18)

> kan du se över funktionerna "Avsluta prenumeration" och "Ändra mejladress" i nyhetsbrev/MailPoet så att de blir
> på svenska och att funktionen är som den ska? Ligger i foten av alla mejl från MailPoet.

## What the footer actually contains (prod, newsletters 39–73, March → November 2026)

```html
<a href="[link:subscription_unsubscribe_url]">Avsluta prenumeration</a> |
<a href="mailto:info@fbhi.se">Ändra mejladress</a>
```

- **"Avsluta prenumeration"** is MailPoet's unsubscribe shortcode. In MailPoet 5.45 `subscription_unsubscribe_url`
  goes to the **confirm step** first (`action=confirm_unsubscribe`: "Confirm you want to unsubscribe" + button
  "Yes, unsubscribe me"). The button then runs `action=unsubscribe` ("You are now unsubscribed", "Accidentally
  unsubscribed?" + resubscribe link, and the **unsubscribe survey**, which is enabled on prod).
- **"Ändra mejladress" isn't a MailPoet function.** It's a plain `mailto:info@fbhi.se` link, so clicking it
  opens a new email to FBHI in the reader's mail program (nothing happens if they have none, e.g. webmail-only on
  some computers). FBHI then has to change the address by hand (MailPoet → Subscribers → edit; MailPoet allows that).
- MailPoet itself **can't let a subscriber change their address**: on the manage page the email field is read-only
  with "Need to change your email address? Unsubscribe using the form below, then simply sign up again." Doing that
  would lose the subscriber's custom fields (organisation, title, membership…), so for FBHI the mailto route plus a
  manual edit is actually the better process.

## Why the pages are in English

- All MailPoet subscription pages (`manage`, `unsubscribe`, `confirm_unsubscribe`, `confirmation`) are **one page,
  10860** (`mailpoet_page`, slug `subscriptions`); there's also a captcha page, 11985. WPML doesn't translate
  `mailpoet_page`.
- MailPoet builds every link as `get_permalink(10860)` + `&mailpoet_router&endpoint=subscription&action=…&data=…`
  (`SubscriptionUrlFactory` → `MailPoetPageResolver::getPermalinkOrHome()` → `WPFunctions::getPermalink()` →
  core `get_permalink()`). The permalink is `https://fbhi.se/?mailpoet_page=subscriptions`, with no `/sv/`, so WPML serves
  the **default language, English** (`lang="en-US"`, site footer in English too). The texts come from MailPoet's PHP
  (`__()`, domain `mailpoet`).
- **Checked on prod with preview URLs** (`data` = `{"preview":1}`, nothing changes): the same URLs with `/sv/` after the
  domain are **fully Swedish**, site footer included:
  - confirm: "Bekräfta att du vill avregistrera" / "Du behöver bara klicka på denna länk…" / "Ja, avregistrera mig"
  - unsubscribed: "Du har nu avslutat prenumerationen." / "Avregistrerad av misstag? …"
  - manage: "Hantera din prenumeration", "Behöver du ändra din e-postadress? Avsluta prenumerationen med hjälp av
    formuläret nedan och registrera dig sedan igen.", custom fields, "Dina listor"
- Prod's `mailpoet-sv_SE` pack (revision 2026-11-02) covers **all visitor-facing strings**, including "Spara ändringar"
  and the survey reasons. The only gap seen is "To manage your subscription, use the link in one of our emails."
  (shown only when someone opens the page without a link token). Dev's pack is older (2026-09-02) and has gaps; dev also
  has MailPoet deactivated.
- `apply_filters( 'wpml_permalink', get_permalink( 10860 ), 'sv' )` on prod returns
  `https://fbhi.se/sv/?mailpoet_page=subscriptions` (captcha page likewise).

## What MailPoet and WPML document (read 2026-11-18)

- MailPoet KB, [Support for Multilingual Plugins](https://kb.mailpoet.com/article/203-support-for-multilingual-plugins):
  "MailPoet does not directly support qTranslate, WPML, or Polylang". Standard newsletters still work. The recommended
  setup is one list + one form per language, one sign-up confirmation email containing all languages, and
  "translate" the Unsubscribe and Manage Your Subscription pages. Each newsletter goes to the list for its language.
  Known WPML issues: Abandoned Cart only in the main language; post notifications filtered by language pull default-language
  posts only; back-end texts may use WPML's translations; and the manage page "is not language-specific". No date
  on the page. It says nothing about how to make the pages render in another language.
- WPML, [MailPoet 3 compatibility](https://wpml.org/plugin/mailpoet-3-new): "not currently compatible with WPML"
  ("has known issues", tested with MailPoet 4.40.0). It repeats MailPoet's three points.
- So there's **no official MailPoet setting** for the language of the subscription pages. MailPoet's texts already come
  in Swedish through the language pack (the "translate the pages" step is done). What's missing is only that WPML
  serves the page in Swedish, which the filter below does. It's a site-specific workaround, and it fits FBHI because
  all newsletters are Swedish (one language, so no per-language lists or forms are needed).
- Multilingual **emails** aren't relevant here: newsletter content is written per email in the editor, and MailPoet has
  no translation layer for it. If English newsletters are ever sent, they'd need their own list, and the pages would then need
  per-language logic (e.g. choose the language from the subscriber's list). Not needed now.

## Plan (awaiting go-ahead)

1. **Child theme (Daniel/Claude):** a small filter in `functions.php` (or `includes/mailpoet.php`):
   `post_type_link` → if the post type is `mailpoet_page`, return `apply_filters( 'wpml_permalink', $url, 'sv' )`.
   One place covers every MailPoet URL: the footer link, the "Ja, avregistrera mig" button, the resubscribe and
   manage links, the double opt-in confirmation page and the captcha page.
   - **Already-sent newsletters are covered too.** With click tracking on (prod: `tracking.level = full`), the email
     only contains a tracking link that stores the shortcode `[link:subscription_unsubscribe_url]`. MailPoet resolves it
     **at click time** (`Statistics/Track/Clicks::processUrl()` → `Link::processShortcodeAction()` →
     `SubscriptionUrlFactory` → `get_permalink()`), so the filter applies to old emails as well. (`Link::processUrl()`
     puts the real URL into the email only when tracking is off.)
   - Verify: dev can only show `get_permalink(10860)` (MailPoet inactive there). On prod after deploy, read-only:
     `SubscriptionUrlFactory::getInstance()->getConfirmUnsubscribeUrl(null)` contains `/sv/`; preview URLs render Swedish;
     Annika sends herself a test and clicks "Avsluta prenumeration" up to the confirm page (without confirming).
2. **Annika, in the MailPoet editor (her template / next newsletter):** keep "Ändra mejladress" as an email link but
   prefill the subject so the requests are easy to recognise, e.g. `mailto:info@fbhi.se?subject=Ändra%20mejladress`.
   Optionally add "Hantera prenumeration" (`[link:subscription_manage_url]`) so readers can update name/organisation/lists
   themselves. Explain that MailPoet can't change the address itself; she changes it in MailPoet → Subscribers.
3. **Side note (not asked for):** the double opt-in email "Confirm your subscription to [site:title]" (newsletter 1) is in
   English. It only matters if people subscribe through a MailPoet form; to check with Annika.

## Revert

Remove the filter and deploy; no data or settings change.
