# WPForms: "Attempt to submit corrupted post data" + "tick every box" on the IWG survey

## What this is

Investigation notes for an intermittent failure on the **International Working Group on
Implementation** entry survey:

- Public page: <https://fbhi.se/implementation-international-working-group/>
- WPForms form id: **14741** (`<form id="wpforms-form-14741">`), page id 14747
- WPForms version at time of investigation: 1.10.0.5

Keep this doc if the issue resurfaces — it records the root cause, the evidence, what was
changed, and what was left as a recommendation.

## Symptoms reported

1. On submit, the form *"kept asking me to revise all the questions with multiple choice
   options. Only if I ticked every box would it let me submit."*
2. After ticking every box and submitting, the page showed **"Attempt to submit corrupted
   post data."**
3. On later attempts the same user could submit normally with only 1–2 boxes ticked. So the
   failure was **intermittent and self-healing across page loads.**

## Root cause (one mechanism, two symptoms)

**Both symptoms are the same failure: the form's correctness depends on WPForms' JavaScript
having initialised before the user submits.** When that JS does not take over the submit, the
form degrades to a plain no-JS HTML submit and two latent problems fire together.

### Symptom 1 — "must tick every box"

- WPForms puts the native HTML `required` attribute on **every individual checkbox** in a
  required group.
- The browser has no concept of a checkbox *group*: each `required` checkbox is validated
  independently, so **native HTML5 validation demands every box be ticked.**
- WPForms normally hides this with JS: at runtime it sets `novalidate` on the form (disabling
  native validation) and uses jQuery-Validate, which *does* treat same-named checkboxes as a
  group where one tick is enough.
- Proven in DevTools: with 1 of 4 boxes ticked in field 100, the other 3 report
  `validity.valueMissing === true` (native = invalid), while jQuery group validation reports
  the single tick as valid.
- The **"Choice Limit"** setting does **not** help — it is a JS-only rule layered on top of a
  checkbox and does not change the native single/multi semantics.

### Symptom 2 — "Attempt to submit corrupted post data"

- There is **no CAPTCHA** on this form (no reCAPTCHA / hCaptcha / Turnstile script, container,
  or token field present). The WPForms doc blames captcha, but here the trigger is the
  **anti-spam token**.
- The form carries `data-token` + `data-token-time`. There is **no `wpforms[token]` hidden
  field in the HTML** — WPForms' JS injects the token **at submit time**, inside its AJAX
  submit handler. Confirmed: the token field is absent from the DOM even after full init.
- Server-side, a missing/invalid token is rejected with **"Attempt to submit corrupted post
  data,"** and **the entry is discarded (not saved).**
- Reproduced directly: POSTing the form payload the way a no-JS browser would (i.e. without the
  JS-injected `wpforms[token]`) returns `<p>Attempt to submit corrupted post data.</p>`.

### Why *this* system makes it likely

- Production is **Nginx + FastCGI page cache** (`x-cache: HIT` confirmed; host: Loopia). The
  cached HTML carries a baked-in, time-stamped token, so even more responsibility shifts onto
  the JS.
- WPForms' own frontend scripts load with **`defer`** (WP core script strategy), so there is
  always a window where the form is interactive before its JS has bound the submit handler.
- At the time of the report, **three** front-end optimizers were active at once (Autoptimize +
  LiteSpeed Cache + Jetpack Boost) — a classic source of occasional broken/partial JS.

A normal, lightly-cached, non-deferred site almost always wins the JS race, so nobody notices.
On this stack the race is occasionally lost, and both problems surface together.

## Likely cause of the JS not initialising (best assessment)

The reporter hit the "tick every box" wall *repeatedly across one session*, then a fresh load
worked — i.e. JS was effectively not running for that whole page load, and a reload fixed it.
That points to a **transient, self-healing asset-load/execution failure**, not a few-ms timing
race. Ranked:

1. **Transient JS error from the (then three) optimizers** — one thrown exception early in the
   deferred chain stops every script after it, so `novalidate` / the submit handler never get
   set. Reload → good cached bundle → works. *Most directly addressed by the cleanup below.*
2. **A script that failed to download or stalled** — ~11 separate JS files, single
   `fbhi.se` origin, no CDN fallback; one stalled/blocked early file (e.g. jQuery) stalls the
   whole deferred chain. Network blips / slow mobile fit "broke once, fine after."
3. **Defer timing race** — possible but less likely; the survey takes seconds to fill.
4. **Stale token on a long-open cached page** — secondary; produces the token error even with
   JS if the page sat cached past the token lifetime.

We could **not** deterministically reproduce which one hit the reporter (transient failures
leave no clean fingerprint).

## What was changed (2026-06)

Production plugin list had three overlapping front-end optimizers. Consolidated to one:

| Plugin | Role on this Nginx box | Action |
| --- | --- | --- |
| `autoptimize` | Active optimizer — minifies + defers JS individually (`autoptimize_single_*.js`), CSS | **Kept** |
| `litespeed-cache` | Page cache **inert** on non-LiteSpeed server; only an async-CSS shim | **Deactivated** (delete pending) |
| `jetpack-boost` | Only the Critical-CSS module was active | **Deactivated** (delete pending) |
| `wp-super-cache` | Dormant second page-cache plugin | Recommend delete |
| `nginx-helper` | Purges the Nginx FastCGI cache | Kept |
| `redis-cache` + `object-cache.php` | Object cache | Kept |

Deletion command (run on prod once deactivation is verified):

```bash
wp plugin delete litespeed-cache jetpack-boost wp-super-cache
wp nginx-helper purge-all   # then clear Autoptimize cache from its settings page
```

### Post-change verification (Chrome DevTools, prod)

- No console errors/warnings.
- WPForms JS fully intact: all 10 scripts load, jQuery-Validate attached, `novalidate` set,
  AJAX submit path live.
- Page renders correctly, no FOUC/layout regression.
- Warm-cache performance: TTFB 114 ms, FCP 364 ms, **LCP 364 ms** (excellent). Losing Jetpack
  Boost's critical CSS had no meaningful impact — the 29 render-blocking CSS files are small,
  minified, HTTP/2-multiplexed and cached.
- Note: there are now **29 render-blocking stylesheets in `<head>` with no inline critical
  CSS.** If slow-connection first paint ever needs hardening, enable Autoptimize **"Aggregate
  CSS files"** (test it — combining can reorder styles); not needed per current numbers.

**The cleanup reduces the *probability* of the JS-not-ready state. It does not deterministically
fix the form — that requires the form-side changes below.**

## Recommended fixes NOT yet applied (the durable fix)

These remove the JS dependency instead of just lowering its odds:

1. **Convert the two single-answer questions from Checkboxes → "Multiple Choice" (radio):**
   - Field **100** — "Are you currently implementing…?" (choice limit 1)
   - Field **101** — "What support…most helpful?" (choice limit 1)
   Radios are a native group: same name + required = "pick exactly one," enforced by the
   browser with no JS. They cannot produce "tick every box."
2. **Field 103** — "What does implementation look like?" (choice limit 8) is a genuine
   multi-select. HTML has **no native "at least one of this group is required"** rule, so a
   *required* multi-select checkbox is inherently JS-dependent. Either make it **not required**,
   or accept the JS dependency.
3. **Anti-spam token (the "corrupted post data" half) — choose one:**
   - **Turn off WPForms' modern anti-spam token for this form and enable the honeypot.** The
     honeypot only checks that a hidden field stayed empty, so a no-JS human still passes —
     unlike the token, which a tokenless (no-JS) submit always fails. *(Recommended.)*
     Note: Turnstile/reCAPTCHA would re-introduce the same JS dependency — don't use those as
     the substitute.
   - Or **keep the token on** and **exclude the form page from the Nginx full-page cache** so
     it always serves a freshly-rendered page (fresh token, clean asset load).

## Field reference (form 14741)

| Field id | Question | Type | Choice limit | Notes |
| --- | --- | --- | --- | --- |
| 100 | Are you currently implementing…? | Checkbox (4) | 1 | every box `required`; should be radio |
| 103 | What does implementation look like? | Checkbox (8) | 8 | genuine multi-select; conditional trigger |
| 101 | What support…most helpful? | Checkbox (7) | 1 | every box `required`; should be radio |

## How to diagnose if it reappears

- **Confirm the JS state:** load the page, in DevTools console check
  `document.querySelector('form.wpforms-form').getAttribute('novalidate')` (should be
  `"novalidate"`) and `!!jQuery(form).data('validator')` (should be `true`). If either is
  false/absent, WPForms JS did not initialise.
- **Reproduce the server rejection:** `POST` a `new FormData(form)` to the form action without a
  `wpforms[token]` field → expect "Attempt to submit corrupted post data."
- **Check cache state:** `curl -sSI <page-url>` and look at `x-cache` (HIT = served from Nginx
  cache).
- **WPForms logs:** WPForms → Tools → **Logs** lists token/corrupted-post rejections with
  timestamps and frequency.
- **Server access log:** around a failure timestamp, check whether any WPForms/jQuery `*.js`
  asset returned non-200 or timed out.
- **Throttled reproduction:** load with slow-3G / CPU throttle in DevTools to watch the form sit
  in its native, pre-init state with a widened submit window.

## References

- WPForms — Resolving the "Attempt to submit corrupted post data" error:
  <https://wpforms.com/docs/resolving-the-attempt-to-submit-corrupted-post-data-error-in-wpforms/>
- See also `CLAUDE.md` → Deployment/Caching (Nginx Helper purge) and the prod = Nginx note.
