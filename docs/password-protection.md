# Password-protected pages: the "Skyddad:" prefix and the protected-excerpt text

Status 2026-09-28: **title prefix and excerpt text reworded site-wide, on dev and prod** (see "Implemented" below).
Password form and `Privat:` left as core has them for now. The guide CPT
follows core's handling (see below), so the change reaches the guides automatically.

## Implemented (2026-09-28, dev + prod)

Agreed with Annika. Site-wide (every password-protected post, not just guides), in `salient-child/functions.php`
section "Password-protected posts", language picked from `get_locale()` (WPML switches it; `sv*` = Swedish,
anything else English):

| Where | Swedish | English |
|---|---|---|
| Title prefix (`protected_title_format`) | `Granskas: <titel>` | `In review: <title>` |
| Excerpt (`gettext`, core msgid checked on WP 7.1.2) | Detta innehåll granskas för tillfället. | This content is currently under review. |

Verified on dev as a visitor: network project AgeWell.de (13013 sv / 13020 en, password-protected on dev) shows the
new title in H1 and `<title>`, both languages; the excerpt checked via PHP in both languages. Guide chapter 13901 given
a temporary password: new title in header, sidebar, start-page card and next-link; new text as intro and card text;
no "Skyddad" left in the HTML. Password removed again afterwards.

Deployed to prod the same day (commit fbff2d1). Nothing public shows it there yet: AgeWell.de is protected on dev
only, and the guides are still drafts. "Granskas" / "In review" will appear on any password-protected post, not
just guides.

Decided 2026-09-28: the prefix **stays after unlocking** (core behaviour, kept on purpose: "Granskas" describes the
content, and whoever has the password is reviewing it). Hiding it once unlocked would be a
`post_password_required( $post )` check in the filter, but then unlocked pages must never be page-cached.

## What WordPress shows today (Swedish site, WP 7.1)

When a post has a password and the visitor has not entered it:

| Where | Text on the site | Comes from (core) |
|---|---|---|
| Title, everywhere `get_the_title()` / `the_title()` is used (front end only) | `Skyddad: <title>` | `protected_title_format` filter, default `__( 'Protected: %s' )` |
| Excerpt, everywhere `get_the_excerpt()` / `the_excerpt()` is used | "Det finns inget utdrag eftersom detta är ett skyddat inlägg." | `get_the_excerpt()` returns `__( 'There is no excerpt because this is a protected post.' )` **before** its `get_the_excerpt` filter runs |
| Content | Password form: "Detta innehåll är lösenordsskyddat. För att visa det, ange lösenordet nedan." / "Lösenord:" | `get_the_password_form()`, filter `the_password_form` |
| Private posts (related) | `Privat: <title>` | `private_title_format` filter, default `__( 'Private: %s' )` |

After the visitor enters the password, WordPress sets the `wp-postpass_<hash>` cookie and the excerpt and content
switch to the real ones. **The title prefix stays**: `get_the_title()` adds it whenever the post has a password
(`! empty( $post->post_password )`), whether or not it is unlocked (checked in WP 7.1.2 source). The cookie holds
the password hash, so every post with the **same password** unlocks at once.

## How to change or remove them (not implemented)

Put the code in `salient-child/functions.php` (theme-level behaviour, not guide-specific), unless Annika wants it
for guides only.

**Title prefix**: the proper filter. It receives the post, so it can be limited to a post type:

```php
// Remove the prefix everywhere:
add_filter( 'protected_title_format', static fn() => '%s' );

// Or reword it, guides only (the format must keep %s):
add_filter( 'protected_title_format', static function ( string $format, \WP_Post $post ): string {
	return 'guide' === $post->post_type ? '🔒 %s' : $format;
}, 10, 2 );
```

Guide templates print titles through `esc_html()`, so the replacement must be plain text (an emoji is fine,
HTML such as an icon `<span>` is not).

**Excerpt text**: no usable filter (the early return skips `get_the_excerpt`). Options:

- `gettext` filter matching the English source string exactly, in the `default` domain: works everywhere (Salient
  blog loops, search results, guide cards and header). Return `''` to show nothing. The guide templates hide the
  intro when it is empty, but other templates may still print an empty wrapper, so check them.
- `the_excerpt` filter only covers `the_excerpt()` callers, not `get_the_excerpt()` (which the guides use), so
  it is not enough on its own.

```php
add_filter( 'gettext', static function ( string $translation, string $text, string $domain ): string {
	if ( 'default' === $domain && 'There is no excerpt because this is a protected post.' === $text ) {
		return 'Ange lösenordet för att läsa.'; // Or '' for nothing.
	}
	return $translation;
}, 10, 3 );
```

The filter runs for every translated string, so keep the check cheap (as above). Limiting it to guides needs
a post-type check (e.g. `get_post_type()` in the loop), which is fragile on index pages that list several posts.
If it should be guides only, it is cleaner to special-case it in `Guide_Frontend::intro()` instead.

**Password form text**: the `the_password_form` filter (args `$output, $post, $invalid_password`), or
`gettext` on its source strings. Before implementing, copy the exact English msgids from
`wp-includes/post-template.php` on the server: core reworded this text in recent versions, and the
filter only matches the exact string.

## Guide CPT: native handling (done 2026-09-28, dev + prod)

The guide module used to read the raw fields, which bypassed the password check. Now it goes through core:

| Where | Before | Now |
|---|---|---|
| Intro/card text (`Guide_Frontend::intro()`) | Raw `post_excerpt`: shown without password | Locked: `get_the_excerpt()`, i.e. core's message. Unlocked: manual excerpt only (D21) |
| Sidebar headings of *other* chapters (`Guide_TOC::items_for_post()`) | Parsed from raw `post_content`: leaked all headings | Empty while locked |
| Auto-appended chapter index on a protected start page (`Guide_Blocks::auto_append_index()`) | Appended below the password form | Not appended while locked (it is part of the content) |
| Titles, content, current page's TOC | Already via `get_the_title()` / `get_the_content()` | Unchanged |

Verified on dev as a visitor with every guide page password-protected: "Skyddad:" titles in header and sidebar,
core excerpt message as the intro, only the password form as content, no cards or sidebar headings, and none of
the intro text anywhere in the page source (meta tags included). Daniel verified the unlocked state in the
browser.

## Open: page cache and the password cookie on prod

Prod runs the Nginx page cache. Check that it **bypasses the cache when the `wp-postpass_*` cookie is set**
(usually part of the standard WordPress `fastcgi_cache_bypass` cookie list). If it does not, an unlocked page
could get cached and be served to everyone. It doesn't matter yet because the guide on prod is still drafts, but check it
before publishing a password-protected page. Dev (LiteSpeed) handled it correctly in Daniel's test.
