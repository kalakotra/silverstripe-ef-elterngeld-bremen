#!/usr/bin/env python3
"""Repair the Bremen "Antrag auf Elterngeld" PDF (Antragsversion August 2025, 36 pages).

The distributed file has no /AcroForm in its catalog and 51 widgets whose parent field
dictionaries are missing (broken cross-reference entries), so pdftk sees no fields at all.
This script rebuilds the /AcroForm and turns every orphaned widget into a named field.
The names below are referenced by the module's field mappings - do not change them.

Usage: python3 bin/repair-pdf.py <original.pdf> <repaired.pdf>
Requires: pypdf >= 4
"""
import sys

from pypdf import PdfReader, PdfWriter
from pypdf.generic import (ArrayObject, DictionaryObject, NameObject, NumberObject,
                           TextStringObject, BooleanObject)

# (page, x0, y0) of the widget rect -> (field name, kind); kind: tx | cb | radio:<group>
ORPHANS = {
    (1, 57, 407): ('1a Vorname Kind', 'tx'),
    (1, 318, 407): ('1a Nachname Kind', 'tx'),
    (1, 57, 213): ('1b Geburtsdatum Kind', 'tx'),
    (2, 56, 129): (None, 'radio:2b Geschlecht AS'),
    (2, 56, 113): (None, 'radio:2b Geschlecht AS'),
    (2, 56, 96): (None, 'radio:2b Geschlecht AS'),
    (2, 56, 80): (None, 'radio:2b Geschlecht AS'),
    (2, 327, 129): (None, 'radio:2b Geschlecht 2E'),
    (2, 327, 113): (None, 'radio:2b Geschlecht 2E'),
    (2, 327, 96): (None, 'radio:2b Geschlecht 2E'),
    (2, 327, 80): (None, 'radio:2b Geschlecht 2E'),
    # Anlage Arbeitgeberbescheinigung
    (25, 57, 336): ('Kind Vorname#ArbG-Bescheinigung', 'tx'),
    (25, 318, 336): ('Kind Nachname#ArbG-Bescheinigung', 'tx'),
    (25, 57, 291): ('Kind Geburtsdatum#ArbG-Bescheinigung', 'tx'),
    (25, 57, 70): ('1 unbefristet', 'cb'),
    (25, 177, 70): ('1 befristet', 'cb'),
    (25, 364, 70): ('1 gekuendigt', 'cb'),
    (26, 57, 576): ('1 Urlaub nein', 'cb'),
    (26, 57, 559): ('1 Urlaub ja', 'cb'),
    (26, 57, 268): ('2 Zuschuss nein', 'cb'),
    (28, 57, 342): ('5 AG Datum', 'tx'),
    # Anlage Wohnsitz im Ausland
    (29, 56, 457): ('Kind Vorname#Wohnsitz', 'tx'),
    (29, 318, 457): ('Kind Nachname#Wohnsitz', 'tx'),
    (29, 57, 413): ('Kind Geburtsdatum#Wohnsitz', 'tx'),
    # Anlage Staatsangehoerigkeit (page 31, first copy)
    (31, 57, 633): ('AS Vorname#Staat1', 'tx'),
    (31, 318, 633): ('AS Nachname#Staat1', 'tx'),
    (31, 57, 468): ('Kind Vorname#Staat1', 'tx'),
    (31, 318, 468): ('Kind Nachname#Staat1', 'tx'),
    (31, 57, 423): ('Kind Geburtsdatum#Staat1', 'tx'),
    (31, 57, 351): ('2g EU#Staat1', 'cb'),
    (31, 74, 276): ('2g Staat EU#Staat1', 'tx'),
    (31, 74, 240): ('2g Freizuegigkeit ja#Staat1', 'cb'),
    (31, 158, 240): ('2g Freizuegigkeit nein#Staat1', 'cb'),
    (31, 57, 219): ('2g anderer Staat#Staat1', 'cb'),
    (31, 74, 177): ('2g Staat anderer#Staat1', 'tx'),
    (31, 57, 125): ('2g staatenlos#Staat1', 'cb'),
    # Anlage Staatsangehoerigkeit (page 32, second copy)
    (32, 57, 633): ('AS Vorname#Staat2', 'tx'),
    (32, 318, 633): ('AS Nachname#Staat2', 'tx'),
    (32, 57, 589): ('AS Geburtsdatum#Staat2', 'tx'),
    (32, 57, 468): ('Kind Vorname#Staat2', 'tx'),
    (32, 318, 468): ('Kind Nachname#Staat2', 'tx'),
    (32, 57, 423): ('Kind Geburtsdatum#Staat2', 'tx'),
    # Anlage Einkuenfte neben dem Elterngeld
    (33, 57, 446): ('Kind Vorname#Einkuenfte', 'tx'),
    (33, 318, 446): ('Kind Nachname#Einkuenfte', 'tx'),
    (33, 57, 402): ('Kind Geburtsdatum#Einkuenfte', 'tx'),
    (34, 57, 631): ('1 erste Taetigkeit Midi-Job', 'cb'),
    (34, 57, 614): ('1 erste Taetigkeit Berufsausbildung', 'cb'),
    (34, 57, 598): ('1 erste Taetigkeit Freiwilligendienst', 'cb'),
    (35, 57, 317): ('2a tatsaechliche Betriebsausgaben', 'cb'),
    (35, 57, 191): ('2b steuerfrei ja', 'cb'),
    (35, 127, 191): ('2b steuerfrei nein', 'cb'),
}

DA = '/BundesSansOffice 10 Tf 0 g'


def main(src: str, dst: str) -> None:
    reader = PdfReader(src, strict=False)
    writer = PdfWriter(clone_from=reader)

    roots: list = []
    seen: set = set()
    groups: dict = {}
    found: set = set()

    for pno, page in enumerate(writer.pages, start=1):
        for ref in page.get('/Annots') or []:
            widget = ref.get_object()
            if widget.get('/Subtype') != '/Widget':
                continue
            node, node_ref = widget, ref
            while node.get('/Parent') is not None:
                parent = node['/Parent'].get_object()
                if not isinstance(parent, DictionaryObject):
                    del node['/Parent']  # dangling reference to a lost field dictionary
                    break
                node_ref, node = node['/Parent'], parent
            if node.get('/T') is not None:
                if node_ref.idnum not in seen:
                    seen.add(node_ref.idnum)
                    roots.append(node_ref)
                continue

            x0, y0 = (round(float(v)) for v in widget['/Rect'][:2])
            key = (pno, x0, y0)
            if key not in ORPHANS:
                raise SystemExit(f'Unknown orphan widget on page {pno} at {x0},{y0}')
            found.add(key)
            name, kind = ORPHANS[key]
            widget[NameObject('/P')] = page.indirect_reference
            if kind.startswith('radio:'):
                groups.setdefault(kind[6:], []).append(ref)
                continue
            widget[NameObject('/T')] = TextStringObject(name)
            if kind == 'tx':
                widget[NameObject('/FT')] = NameObject('/Tx')
                widget[NameObject('/DA')] = TextStringObject(DA)
            else:
                widget[NameObject('/FT')] = NameObject('/Btn')
                widget[NameObject('/V')] = NameObject('/Off')
                widget[NameObject('/AS')] = NameObject('/Off')
            roots.append(ref)

    missing = set(ORPHANS) - found
    if missing:
        raise SystemExit(f'Orphan widgets not found: {sorted(missing)}')

    for name, kids in groups.items():
        parent = DictionaryObject({
            NameObject('/FT'): NameObject('/Btn'),
            NameObject('/Ff'): NumberObject(49152),  # radio + no toggle to off
            NameObject('/T'): TextStringObject(name),
            NameObject('/V'): NameObject('/Off'),
            NameObject('/Kids'): ArrayObject(kids),
        })
        parent_ref = writer._add_object(parent)
        for kid in kids:
            w = kid.get_object()
            w[NameObject('/Parent')] = parent_ref
            w[NameObject('/AS')] = NameObject('/Off')
            _ascii_states(w)
        roots.append(parent_ref)

    helv = writer._add_object(DictionaryObject({
        NameObject('/Type'): NameObject('/Font'),
        NameObject('/Subtype'): NameObject('/Type1'),
        NameObject('/BaseFont'): NameObject('/Helvetica'),
        NameObject('/Encoding'): NameObject('/WinAnsiEncoding'),
    }))
    writer._root_object[NameObject('/AcroForm')] = writer._add_object(DictionaryObject({
        NameObject('/Fields'): ArrayObject(roots),
        NameObject('/DA'): TextStringObject(DA),
        NameObject('/NeedAppearances'): BooleanObject(False),
        NameObject('/DR'): DictionaryObject({
            NameObject('/Font'): DictionaryObject({
                NameObject('/BundesSansOffice'): helv,
                NameObject('/Helv'): helv,
            }),
        }),
    }))

    # 16 pt in a 22 pt high box clips almost every value: use 10 pt for all text fields
    for ref in roots:
        _set_da(ref.get_object())

    with open(dst, 'wb') as fh:
        writer.write(fh)
    print(f'{len(roots)} fields, {len(found)} orphan widgets repaired -> {dst}')


def _ascii_states(widget) -> None:
    """The on-state names are raw UTF-8 bytes ("m\xc3\xa4nnlich"), which pdftk reads as Latin-1 ("mÃ¤nnlich"):
    rename them to ASCII so the field mapping can address them ("maennlich")."""
    ap = widget.get('/AP')
    if ap is None:
        return
    for kind in ('/N', '/D'):
        states = ap.get_object().get(kind)
        if states is None:
            continue
        states = states.get_object()
        for key in list(states.keys()):
            ascii_key = key.replace('ä', 'ae').replace('ö', 'oe').replace('ü', 'ue').replace('ß', 'ss')
            if ascii_key != key:
                states[NameObject(ascii_key)] = states.raw_get(key)
                del states[key]


def _set_da(node) -> None:
    if node.get('/FT') == '/Tx' or '/DA' in node:
        node[NameObject('/DA')] = TextStringObject(DA)
    for kid in node.get('/Kids') or []:
        _set_da(kid.get_object())


if __name__ == '__main__':
    if len(sys.argv) != 3:
        raise SystemExit(__doc__)
    main(sys.argv[1], sys.argv[2])
