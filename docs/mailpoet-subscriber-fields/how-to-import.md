# How to: WPForms export → MailPoet import with subscriber fields

Walk-through for the routine (Daniel first, on the test list; then Annika on the real list). Background and
what was set up: [2026-09-plan.md](../archive/mailpoet-subscriber-fields-2026-09/2026-09-plan.md).

Fields that exist in MailPoet (MailPoet → Custom Fields): `Organisation`, `Titel/Ansvarsområde`,
`Avdelning/Enhet` (text) and `Medlemskap i FINGER-nätverket` (checkbox, 1 = ticked).

## A. Export from WPForms

1. WPForms → Tools → Export.
2. "Select a Form": pick the registration form (e.g. *Anmälan till online-seminariet den 30 september 2026*).
3. "Form Fields": tick exactly **E-postadress, Förnamn, Efternamn, Organisation, Titel/Ansvarsområde,
   Avdelning/Enhet, Medlemskap i FINGER-nätverket**. Leave "Additional Information" unticked (Entry ID etc.
   would become extra columns you must ignore later).
4. "Export Options": either format works. Untick "Export in Microsoft Excel (.xlsx)" for a CSV straight away,
   or tick it if you prefer to edit in Excel first. "Separate dynamic choices into individual columns" does not
   matter for these fields.
5. Optional: Date Range / Search to limit to the latest registrations.
6. Download.

Column order follows the form, so Medlemskap usually comes first and E-postadress somewhere in the middle.
That is fine, MailPoet finds the e-mail column by content.

## B. Fix the file in Excel

1. **Medlemskap column** contains the whole answer sentence. Turn it into 1/0:
   - insert a helper column, formula for row 2 (adjust the column letter):
     Swedish Excel `=OM(ÄRFEL(SÖK("inte";A2));1;0)` · English Excel `=IF(ISERROR(SEARCH("inte",A2)),1,0)`
   - "Jag vill bli medlem", "Jag är redan medlem", "…osäker på om jag anmält mig" → 1;
     "Jag vill inte bli medlem just nu" → 0; empty → 0.
   - fill down, copy the helper column, **paste as values** over the original column, delete the helper.
   - Header must stay exactly `Medlemskap i FINGER-nätverket`.
   - Older exports where Medlemskap was a checkbox have four columns "Medlemskap i FINGER-nätverket: …":
     1 if any of the first three is non-empty, else 0; then delete the four and keep one column with the
     exact header above.
2. Optional but saves two clicks: rename `Förnamn` → `First name` and `Efternamn` → `Last name`.
3. Remove any column you do not want written into MailPoet (a mapped empty column wipes the old value).
4. Save as CSV. MailPoet does not accept .xlsx.
   - **Google Sheets** (Daniel): File → Import the export (or open the .csv), edit, then
     File → Download → *Comma Separated Values (.csv)*. Always UTF-8 with commas. Formula in a Swedish-locale
     sheet uses semicolons: `=IF(ISERROR(SEARCH("inte";A2));1;0)`; English locale uses commas.
   - **Excel / Microsoft 365** (Annika, probably): File → Save As / Save a Copy → file type
     **"CSV UTF-8 (kommaavgränsad) (*.csv)"**, not plain "CSV (kommaavgränsad)" (that one is not UTF-8 and
     mangles å/ä/ö). Swedish Excel writes semicolons as separators; MailPoet detects that automatically.
     Excel may warn about losing features when saving as CSV; that is expected.

For Daniel's own test: export the real form, keep the header row, delete all data rows, add one row with
`db+mailpoet-test@cyberix.se` (already exists in the test list, so this exercises the *update* path) or a
fresh `db+test2@cyberix.se` (exercises the *new subscriber* path), fill the other cells with anything.

## C. Import into MailPoet

1. MailPoet → Subscribers → **Import** (button top right).
2. Step "clean list": read, click **Got it, I'll proceed to import**.
3. Method: **Upload a file** → choose the CSV → Next step.
4. Only when the file has **more than 100 rows** an extra step asks where the addresses come from: answer
   *existing list* and that you e-mailed them recently (registrations are fresh). Answering "over a year ago"
   shows a clean-your-list warning that has to be clicked through.
5. **Match data** table, one dropdown per column. Check every dropdown:
   - E-postadress → *Email* (automatic)
   - Organisation, Titel/Ansvarsområde, Avdelning/Enhet, Medlemskap i FINGER-nätverket → automatic, by name
   - **Förnamn → First name, Efternamn → Last name: pick by hand** (they default to "Ignore field…") unless
     the headers were renamed in step B.2
   - anything else → *Ignore field…*
   - never use "Create new field…" here by accident.
6. "Pick one or more lists": for the test **Annika & Elin test**; for real use *Nyhetsbrev, Svenska
   FINGER-nätverket* (or whatever the list is called then).
7. Tags: optional, e.g. the seminar name, applied to everyone in the file.
8. "Update existing subscribers' information": **Yes**. New subscribers status: *Subscribed*.
   Existing subscribers status: *Don't update* (keeps unsubscribed people unsubscribed).
9. **Import**. The result page says "N subscribers added / N existing subscribers were updated".
10. Check one person: Subscribers → search the address → the profile shows the three text fields and the
    Medlemskap checkbox.

## What to look out for (the notes for Annika)

- The file is the truth for every column you map. With "update existing = Yes", a mapped empty cell
  overwrites the earlier value, and a `0` in Medlemskap un-ticks someone who was ticked before. Map only
  columns that carry data for that form; set the rest to "Ignore field…".
- Förnamn/Efternamn are not recognised automatically. Pick First name / Last name every time (or rename the
  headers to English before saving).
- The Medlemskap column must be 1/0 (or empty = not ticked), not the sentence.
- CSV only; save as CSV UTF-8 from Excel. Never import the .xlsx.
- If a header is misspelt the column silently shows "Ignore field…" instead of the custom field. Check
  the Match data row before clicking Import.
- Existing subscribers keep their status (unsubscribed stays unsubscribed) as long as "Existing subscribers
  status" stays on *Don't update*.
- Tags apply to the whole file, not per person.
- New fields, or changes to these, are made under MailPoet → Custom Fields (Trash there restores
  "Country"/"Project" if ever needed).
