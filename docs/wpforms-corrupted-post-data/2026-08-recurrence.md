# Recurrence 2026-08-17: same form, same mechanism, two new findings

Follow-up to [README.md](README.md) (the 2026-06 investigation). Read that first —
the root-cause analysis there still holds and is not repeated here.

## What happened

A visitor (Spanish browser locale) got the "tick every box" wall on the same IWG form
(**form 14741**, page `/implementation-international-working-group/`): a native browser
validation tooltip ("Selecciona esta casilla de verificación si quieres continuar")
anchored near the top of the page while their visible checkboxes were already ticked.
Screenshot was provided (kept out of git in `incoming/`).

**Why it recurred:** the 2026-06 round only shipped the optimizer cleanup (probability
reduction). The **durable form-side fixes were never applied** — confirmed by reading the
live form config on 2026-08-17:

- Fields **100** and **101** still checkbox groups with `required` on every box
  (choice limit 1) — the native "tick every box" trap.
- The modern **anti-spam token still enabled** — tokenless (JS-dead) submits still die
  with "Attempt to submit corrupted post data".
- Form was edited 2026-07-13; field **125** ("What support…", choice limit 2, required
  checkbox) was added since the June doc, so its field table is partly outdated
  (June's "field 101 = What support" is now field 125; 101 is "What motivates").
- WPForms JS verified healthy on current page loads (novalidate set, validator attached)
  — the failure remains the intermittent per-visitor JS race described in June.

## New finding 1 — hidden `required` conditional textareas (worse than June knew)

The three conditional "If Other, please describe briefly" textareas (fields **98, 120,
126**) are served with `required` **and** `style="display:none"`. In the JS-dead state,
Chrome still validates hidden controls: submission is blocked on an invisible empty
field ("invalid form control is not focusable" behavior), and the validation bubble
anchors to odd positions — matching the reported screenshot. So in the JS-dead state
**even ticking every box cannot get the form through.**

## New finding 2 — notification email is broken (unrelated to the race)

The Default Notification (to annika.norell.benson@fbhi.se) has a message template
referencing field IDs 26/27/24/21/4/25 — leftovers from the older form this one was
duplicated from. None of those fields exist in form 14741, so **notification emails
arrive essentially empty**. Fix: use `{all_fields}`.

Also: WPForms logging was found **disabled** — no server-side trail of token
rejections. Enable it (WPForms → Tools → Logs) to capture future failures.

## Sequencing note

WPForms was updated 1.10.0.5 → **2.0.0.4** (2026-08-18, see
[../archive/updates-2026-08/README.md](../archive/updates-2026-08/README.md)) **before** applying the
form fixes, so they land on the new version once. Verified: 2.0 did not change the
served form markup (same per-checkbox `required`, no server-side `novalidate`, no token
field) — the June analysis and the fix list below carry over unchanged.

## Fix list (applied via wp-admin form builder)

1. Field 100 → Multiple Choice (radio), required. Native radio group = "pick one"
   with zero JS dependency.
2. Field 101 → Multiple Choice (radio), required; re-point field 126's conditional
   logic at the new field's "Other (please specify below)".
3. Field 103 → not required (native HTML cannot require "at least one of" a checkbox
   group; keeping it required keeps the JS dependency).
4. Field 125 → not required (same reason; description already says "select 1-2").
5. Fields 98, 120, 126 ("If Other…") → not required (removes the hidden-required
   blocker; they're only shown for "Other" anyway).
6. Anti-spam: modern token **off**, classic honeypot **on** (honeypot doesn't punish
   no-JS humans; do NOT substitute a captcha — that re-adds the JS dependency).
7. Notification: message → `{all_fields}`; recipient temporarily → db@cyberix.se for
   testing (restore to the intended recipient after verification).
8. Enable WPForms logging (Tools → Logs) with spam/errors/security types.

Status: **OPEN — monitoring (2026-08-18).** The update round (all plugins incl.
WPForms 2.0.0.4, WP core 7.0.4) is complete and verified — see
`../archive/updates-2026-08/` — and did not change the form's failure mechanics.
The fixes above are fully specified but deliberately not applied yet; Daniel will
apply them in the form builder **if the problem persists/recurs**. A step-by-step
builder walkthrough matching the fix list exists in the 2026-08-18 session notes;
the fix list above is self-sufficient to redo it.
