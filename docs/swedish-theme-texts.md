# Swedish texts for Salient (theme + salient-core) on /sv/

Salient ships **no Swedish translation**: `themes/salient/lang/` only has `default.po/.mo` and `nectar_salient_strings.pot`,
there is nothing in `wp-content/languages/themes/`, and salient-core has no language files. WPML String Translation has scanned
1,510 `salient` strings but holds a Swedish translation for just **1** of them (checked on prod 2026-11-18). So any
theme text shows in English on Swedish pages unless it's translated somewhere.

We translate the texts visitors actually see with `gettext` filters in `salient-child/functions.php` (in git, deployed with
`upload.sh`), on Swedish pages only (`fbhi_is_swedish_locale()`). English pages are untouched. Not WPML String Translation,
which stores them in the database outside git.

| Section in functions.php | Domain | Texts → Swedish | Since |
| --- | --- | --- | --- |
| Salient Post Grid: Swedish "Load More" button and "All" filter | `salient-core` | Load More → Visa fler, All → Alla | 2026-11-18 |
| Salient search: Swedish texts (`fbhi_salient_search_sv_texts()`) | `salient` | Search → Sök, search → sök, Start Typing... → Börja skriva …, Hit enter to search or ESC to close → Tryck Enter för att söka eller Esc för att stänga, Close Search → Stäng sökningen, Results For → Resultat för, results found → träffar, Page → Sida, Blog Post → Inlägg, Previous → Föregående, Next → Nästa, Sorry, no results were found. → Tyvärr hittades inga resultat., Please try again with different keywords. → Försök igen med andra sökord., Search... → Sök …, Search for: → Sök efter: | 2026-11-18 |
| Password-protected posts (`protected_title_format`, `gettext` default domain) | core | Protected: → Granskas: / In review:, protected excerpt text | 2026-09-28 |

Not via `gettext`: the prev/next links at the bottom of kommun and network-project singles
(`includes/partials/shared/bottom-post-navigation.php`) use their own "Föregående"/"Nästa" vs "Previous"/"Next", because
WP core's Swedish translation lacks a bare "Previous".

Sources of the search texts: `includes/header-search.php` (overlay), `search.php` (results, labels per post type; custom post
types such as kommuner get no label), `nectar/helpers/pagination.php` (Previous/Next), `searchform.php` (search form on the
no-results page and in widgets). `Previous`/`Next`/`Search` in the `salient` domain also appear in blog pagination and
search widgets; translating them there on Swedish pages is intended.

Verified on prod 2026-11-18 (after deploy + purge): overlay on /sv/ (placeholder "Sök", "Tryck Enter …", screen-reader
texts), /sv/?s=kommun (41 träffar, Sida, Nästa), page 2 (Föregående), no-hits page (Tyvärr …, "Sök …" box); /?s=project
still English.

**If more theme texts show up in English:** add them to the matching section (check the exact source string and domain in
`reference/theme/salient` or `reference/plugins/salient-core`). If it grows large, switch to a real Swedish `.po/.mo` for
the `salient` domain in the child theme instead.

## Other English on Swedish pages, noticed 2026-11-18 (not fixed, not theme code)

- **Cookie banner (Complianz):** "Manage Cookie Consent", the description paragraph and "Functional" are English on /sv/,
  while "Alltid aktiv" is Swedish. These are Complianz banner texts (translatable per language via Complianz/WPML String
  Translation, i.e. content/settings).
- **Swedish home page title:** the browser tab on /sv/ reads "Home - FBHI" (page title or AIOSEO title of the Swedish front page).
