#!/usr/bin/env python3
"""Generate salient-child/languages/sv_SE.po, sv_SE.mo and the JED .json files for the
guide editor scripts. No msgfmt needed. Run from the repo root:  python3 docs/guides/tools/make-translations.py
Add strings to T below (context strings as (context, msgid) tuples) and to JS1/JS2 for editor scripts."""
import struct, json, hashlib, os, time
ROOT = os.path.join(os.path.dirname(__file__), '..', '..', '..', 'salient-child')
LANG = os.path.join(ROOT, 'languages')
T = {
 ('post type general name','Guides'):'Guider', ('post type singular name','Guide'):'Guide', ('admin menu','Guides'):'Guider',
 ('add new on admin bar','Guide page'):'Guidesida',
 'Add New':'Lägg till ny', 'Add New Guide Page':'Lägg till ny guidesida', 'New Guide Page':'Ny guidesida', 'Edit Guide Page':'Redigera guidesida',
 'View Guide Page':'Visa guidesida', 'All Guide Pages':'Alla guidesidor', 'Search Guide Pages':'Sök guidesidor', 'Parent Guide Page:':'Överordnad guidesida:',
 'No guide pages found.':'Inga guidesidor hittades.', 'No guide pages found in Trash.':'Inga guidesidor i papperskorgen.',
 'Guide page published.':'Guidesidan har publicerats.', 'Guide page updated.':'Guidesidan har uppdaterats.', 'Guide Page Attributes':'Attribut för guidesida',
 'Digital handbooks: a root guide page with chapters as child pages.':'Digitala handböcker: en startsida för guiden med avsnitt som undersidor.',
 'Chapter title':'Avsnittets titel', 'Guide title':'Guidens titel',
 'Chapter accent colour (hex).':'Avsnittets accentfärg (hex).', 'Chapter designation shown above the title, e.g. "Avsnitt 1".':'Avsnittets beteckning som visas ovanför titeln, t.ex. ”Avsnitt 1”.',
 'Teal':'Petrol', 'Green':'Grön', 'Yellow':'Gul', 'Peach':'Persika', 'Navy':'Marinblå',
 'Label':'Etikett', 'Colour':'Färg', '(inherited)':'(ärvd)',
 'Info box':'Faktaruta', 'Callout (chapter colour)':'Markerad ruta (avsnittets färg)', 'Checklist':'Checklista', 'Print page break':'Sidbrytning vid utskrift',
 'FBHI Guide':'FBHI-guide', 'A neutral box for background facts, tools or tips.':'En neutral ruta för bakgrundsfakta, verktyg eller tips.',
 'Good to know':'Bra att veta', 'Write the box text here.':'Skriv rutans text här.',
 'Callout with questions':'Markerad ruta med frågor', 'A box in the chapter colour, for reflection questions such as "Fundera på:".':'En ruta i avsnittets färg, för reflektionsfrågor som ”Fundera på:”.',
 'Think about:':'Fundera på:', 'First question?':'Första frågan?', 'Second question?':'Andra frågan?',
 'Numbered checklist box, typically at the end of a chapter.':'Numrerad checklista i en ruta, vanligtvis i slutet av ett avsnitt.', 'First item':'Första punkten', 'Second item':'Andra punkten',
 'Invisible on screen; starts a new page when printing.':'Syns inte på skärmen; ger ny sida vid utskrift.',
 'Guide index':'Guidens innehåll', 'Guide index — generated automatically from the chapter pages.':'Guidens innehåll – skapas automatiskt från avsnittssidorna.',
 'Heading':'Rubrik', 'Leave empty for the default heading.':'Lämna tomt för standardrubriken.',
 "The guide's chapter cards, generated automatically from the chapter pages. Added after the content by default; place this block to choose where it appears.":'Guidens avsnittskort, som skapas automatiskt från avsnittssidorna. Läggs till efter innehållet som standard; placera blocket för att välja var det ska visas.',
 'Contents':'Innehåll', 'Chapters':'Avsnitt', 'Read more':'Läs mer', 'Read more: %s':'Läs mer: %s',
 'Start':'Start', 'Guide navigation':'Guidens navigation', 'Guide pages':'Guidens sidor',
 'Previous':'Föregående', 'Next':'Nästa', 'Back to the guide':'Tillbaka till guidens start', 'Last updated:':'Senast uppdaterad:', 'Print this page':'Skriv ut sidan',
 'Guide page':'Guidesida', 'Accent colour':'Accentfärg', 'Shown above the title and on the index card, e.g. "Avsnitt 1".':'Visas ovanför titeln och på innehållskortet, t.ex. ”Avsnitt 1”.',
 'Inherited by sub-pages of this chapter. Leave empty to inherit from the parent.':'Ärvs av undersidor till detta avsnitt. Lämna tomt för att ärva från överordnad sida.',
 'Optional colour for the guide start page. Chapters set their own colour.':'Valfri färg för guidens startsida. Avsnitten anger sina egna färger.',
}
JS1 = ['Guide page','Label','Shown above the title and on the index card, e.g. "Avsnitt 1".','Accent colour','Inherited by sub-pages of this chapter. Leave empty to inherit from the parent.','Optional colour for the guide start page. Chapters set their own colour.']
JS2 = ['Guide index','Heading','Leave empty for the default heading.','Guide index — generated automatically from the chapter pages.']
os.makedirs(LANG, exist_ok=True)
def q(s): return '"'+s.replace('\\','\\\\').replace('"','\\"').replace('\n','\\n')+'"'
po=['msgid ""','msgstr ""','"Project-Id-Version: Salient Child Theme (FBHI Guides)\\n"','"Language: sv_SE\\n"','"MIME-Version: 1.0\\n"',
 '"Content-Type: text/plain; charset=UTF-8\\n"','"Content-Transfer-Encoding: 8bit\\n"','"Plural-Forms: nplurals=2; plural=(n != 1);\\n"','"X-Domain: salient-child\\n"','']
for k,v in T.items():
    if isinstance(k,tuple): po += ['msgctxt '+q(k[0]), 'msgid '+q(k[1])]
    else: po.append('msgid '+q(k))
    po += ['msgstr '+q(v), '']
open(os.path.join(LANG,'sv_SE.po'),'w').write('\n'.join(po))
entries=[((k[0]+'\x04'+k[1]) if isinstance(k,tuple) else k).encode() for k in T]
entries=sorted(zip(entries,[v.encode() for v in T.values()]))+[]
entries.append((b'', b'Content-Type: text/plain; charset=UTF-8\nLanguage: sv_SE\nPlural-Forms: nplurals=2; plural=(n != 1);\n'))
entries.sort(key=lambda e:e[0])
n=len(entries); ids=b''; strs=b''; offs=[]
for k,v in entries:
    offs.append((len(ids),len(k),len(strs),len(v))); ids+=k+b'\0'; strs+=v+b'\0'
keystart=7*4+16*n; valstart=keystart+len(ids)
mo=struct.pack('Iiiiiii',0x950412de,0,n,7*4,7*4+8*n,0,0)
mo+=b''.join(struct.pack('Ii',l,keystart+o) for o,l,_,_ in offs)+b''.join(struct.pack('Ii',l,valstart+o) for _,_,o,l in offs)+ids+strs
open(os.path.join(LANG,'sv_SE.mo'),'wb').write(mo)
def jed(strings, path):
    data={'translation-revision-date':time.strftime('%Y-%m-%d %H:%M+0000'),'generator':'make-translations.py','domain':'messages',
          'locale_data':{'messages':{'':{'domain':'messages','lang':'sv_SE','plural-forms':'nplurals=2; plural=(n != 1);'}}}}
    for s in strings: data['locale_data']['messages'][s]=[T[s]]
    fn=os.path.join(LANG,'salient-child-sv_SE-'+hashlib.md5(path.encode()).hexdigest()+'.json')
    json.dump(data, open(fn,'w'), ensure_ascii=False, indent=0); return fn
jed(JS1,'assets/guides/guides-editor.js'); jed(JS2,'includes/guides/blocks/guide-index/editor.js')
print('wrote', n, 'entries')
