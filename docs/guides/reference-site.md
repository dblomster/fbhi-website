# Reference site — what "Inget att vänta på" actually does

Observed 2026-09-02 with Chrome (desktop 1400px and mobile 390px). Saves re-inspecting.

Index: https://jamstalldhetsmyndigheten.se/mans-vald-mot-kvinnor/att-forebygga-mans-vald-mot-kvinnor/inget-att-vanta-pa-som-digital-handbok/
Chapter example: …/inget-att-vanta-pa-som-digital-handbok/initiera-samverkan/

## Index page
- Left column (about two thirds): tinted intro band (pink) containing a kicker ("METODSTÖD"), the
  handbook title, several intro paragraphs, a "Ladda ner som PDF" section and one photo.
- Right column: heading "HANDBOKEN INGET ATT VÄNTA PÅ", then a stacked list of dark-plum buttons:
  Start, Viktiga utgångspunkter…, Steg 1–5, Fördjupning, Om handboken. Chapters have a chevron.
- Below, full column width: heading "5 steg för ett våldsförebyggande arbete" and five stacked cards.
  Each card: large numeral in a circle (1–5), small-caps label "STEG 1:", bold title, 2–4 lines of
  description, "Läs mer" button. Card background is the chapter's colour at low tint (peach, mint,
  lilac, blue-grey, pink). No images on cards.
- Then tags, "Senast uppdaterad: 08:27 - 13 juni 2024", and two buttons: Dela / Skriv ut.
- Unnumbered chapters (Viktiga utgångspunkter, Fördjupning, Om handboken) are in the sidebar but not
  among the five cards.

## Chapter page (Steg 1)
- Breadcrumb row (we skip this, D13).
- Header band in the content column, tinted with the chapter colour: small-caps label "STEG 1:",
  big title "INITIERA SAMVERKAN", intro paragraph. The intro text is **word for word** the same as
  the card description on the index page.
- Body: long article, H2 headings numbered by the editors in the text ("1.1 …", "1.13 Steg 1: Checklista").
  Components seen: interview sections (photo + heading + Q/A paragraphs), info boxes ("Verktyget
  beredskap för förändring"), a diagram image, a "Nationella stödkontakter" list, and a tinted
  checklist box at the end with a numbered list of questions.
- Sidebar (right, `position: sticky`): same chapter list as the index; the **current chapter is
  expanded** and lists all its H2 headings; the active heading is underlined as you scroll.
  Sidebar contains no separate "Contents" block — the TOC lives inside the current chapter's row.
- Bottom: "Senast uppdaterad: 07:54 - 21 augusti 2026", Dela / Skriv ut buttons, then a large
  full-width teaser band for the next chapter ("Beskriv våld som problem").
- Headings have **no** ids in the HTML; their TOC links presumably use generated anchors.

## Mobile (390px)
- Order: breadcrumb → "HANDBOKEN INGET ATT VÄNTA PÅ" heading → one plum dropdown showing the current
  chapter name ("Steg 1: Initiera samverkan") with chevron → tinted chapter header → content.
- The dropdown replaces the whole sidebar (chapters + TOC).

## Takeaways applied in plan.md
- One sidebar component doubles as chapter nav and TOC (D12).
- Index generated automatically after the intro (D17).
- Shared text for card description and header intro (Q1 default).
- Tinted header band and tinted cards from one chapter colour (D9).
- Print button and last-updated line at the bottom (D14, D8).
