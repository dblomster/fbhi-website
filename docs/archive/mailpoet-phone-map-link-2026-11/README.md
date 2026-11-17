# MailPoet newsletter: unwanted map link on phones (2026-11)

**Status: CLOSED 2026-11-18.** Closed by replying to Annika with our explanation of what happened on her
device and a fix she can make in the editor. No change on the site.

## What Annika reported (2026-11-17)

In test sends of the newsletter "Hösthälsningar från FBHI", some words in the last item (the GIH course) were
shown as a link: "…av och vid kognitiv sjukdom" at the end of the heading, plus "Jobbar du inom" at the start of
the next sentence. Tapping it opened a map, and she and a colleague got different places, none connected to
them. The link appeared on phones only, not on a computer, and she could not remove it in MailPoet.

## Findings

- **The link is not in the newsletter.** The email's HTML has no `<a>` around those words. We also read the
  stored body of every version in MailPoet on prod, read-only via Novamira `execute-php` (`mailpoet_newsletters`
  IDs 56–64, including draft 58). The heading and paragraph are plain text in all of them.
- **The phone's mail app creates it.** iPhone Mail (and sometimes the Gmail app) scans text for things that look
  like addresses, phone numbers or dates and makes them tappable ("data detectors"). Here it wrongly took the end
  of the heading plus the start of the next sentence for an address. The link crosses from the `<h3>` into the
  next paragraph, which only this kind of text scanning would do.
- **Different places per person:** the map searches for the "address" near the reader's location.

## Advice given to Annika

1. Turn the heading into a real link, such as to the GIH course page. Phones leave existing links alone, so the
   detection stops. Rewording the start of the first sentence would also work.
2. Send a new test to her phone to check, because the detection is a guess and can't be predicted exactly.

**Side issue, reported at the same time:** in the "Kommuner berättar" item, two Word bullet symbols set in the
Wingdings font show on phones as a stray "I" and "3" in front of the links. She should delete them or replace
them with an ordinary arrow (→ / ▸).

## If it keeps happening (not done)

MailPoet's template already contains `<meta name="format-detection" content="telephone=no">`. A child-theme
MailPoet filter could add `address=no, date=no` and a CSS rule for `a[x-apple-data-detectors]` that removes the
link styling. iOS Mail doesn't always honour `address=no`, and the CSS only hides the styling (the words stay
tappable), so this would reduce the problem rather than remove it. The MailPoet hook has not been looked up yet.
The editor fix above is the reliable one.
