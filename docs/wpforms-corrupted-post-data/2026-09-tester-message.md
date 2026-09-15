# Message to the tester (form 15199, 2026-09-15)

Sent 2026-09-15 with normal email delivery (the notification goes to the usual recipient) after the page cache was purged and logging was enabled. Swedish; English summary at the end.

---

**Sent by Daniel 2026-09-15 (core text; the mail also covered other points from the discussion):**

Problemet med anmälningsformuläret verkar vara att JavaScript inte körs korrekt för vissa besökare. Varför vet jag inte i nuläget. Jag gjorde därför två saker: dels byggde jag om formuläret så att det fungerar bättre utan JavaScript, dels slog jag på loggning så att det förhoppningsvis går att lista ut varför vissa besökare drabbas av problemet.

I formuläret på https://fbhi.se/sv/online-seminarium-30-september-2026/ användes kryssrutor (flervalsfält) med en begränsning till ett alternativ för tre frågor där man bara ska kunna välja ett svar. De tre frågorna är nu utbytta mot "Multiple Choice"-fält, alltså runda radioknappar där endast ett alternativ kan vara markerat. Det är rätt typ av fält för en enkelvalsfråga och fungerar även när JavaScript inte laddas.

Det gäller följande frågor:

1. Online-seminariet 30 september kl. 13–15
2. Medlemskap i FINGER-nätverket
3. FINGER-inspirerat arbete i min organisation/kommun/region

Frågan om rollbeskrivning, ”Detta/dessa alternativ beskriver min roll”, är oförändrad. Där ska man fortfarande kunna kryssa i flera alternativ.

Jag har bara ändrat i just detta formulär. Det finns fler formulär som behöver samma ändring, men jag vill att vi testar det här först. Kan du göra en testanmälan?

**Suggested test steps (from the draft, for reuse with the next tester):**

1. Öppna sidan i din vanliga webbläsare, gärna även i mobilen om du har möjlighet.
2. Kontrollera att de tre frågorna visar runda knappar och att det bara går att välja ett alternativ per fråga. Klicka på ett annat alternativ och se att det första avmarkeras.
3. Fyll i hela formuläret som en riktig anmälan, med ditt eget namn och din e-postadress. Skriv "TEST" i fältet Titel/Ansvarsområde så att vi hittar anmälan efteråt.
4. I frågan om din roll: kryssa i minst två alternativ.
5. Prova först att skicka utan att ha valt något i fråga 2. Du ska få ett felmeddelande vid just den frågan, inte ett krav på att kryssa i allt.
6. Välj ett alternativ och skicka. Du ska få ett tackmeddelande direkt på sidan.

Återkoppling som efterfrågas: fungerade allt, vilken webbläsare och enhet, gärna en skärmbild.

---

**English summary for the record:** JavaScript appears not to run correctly for some visitors, cause unknown. Three single-answer questions on the 30 September registration form were built as checkbox groups limited to one choice; they are now Multiple Choice (radio) fields, which is the correct control and works without JavaScript. Only this form is changed so far. The tester fills in a real registration marked "TEST", first tries submitting with question 2 unanswered to see a friendly per-question error, then submits and reports browser and device. Logging is being enabled to find the cause. Turning off parts of the spam protection remains a last resort.
