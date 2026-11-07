#!/usr/bin/env python3
"""Inventory of the repaired Elterngeld PDF -> <work>/fields_<doc>.tsv per page range.
Columns: page, FT, full name, state (one row per on-state), MaxLen, x, y, TU.
States come from pdftk dump_data_fields_utf8 (pypdf mangles non-ASCII state names)."""
import subprocess, sys
from pypdf import PdfReader

pdf, work = sys.argv[1], sys.argv[2]
DOCS = {'antrag': range(1, 24), 'employer': range(25, 29), 'abroad': range(29, 31),
        'citizenship': range(31, 33), 'income': range(33, 36), 'other': [24, 36]}

out = subprocess.run(['pdftk', pdf, 'dump_data_fields_utf8'], capture_output=True, text=True).stdout
fields, cur = {}, None
for line in out.splitlines():
    if line == '---':
        cur = {}
        continue
    if ': ' not in line:
        continue
    k, v = line.split(': ', 1)
    if k == 'FieldName':
        cur['name'] = v; fields[v] = cur; cur.setdefault('states', [])
    elif k == 'FieldType':
        cur['type'] = v
    elif k == 'FieldStateOption' and v != 'Off':
        cur.setdefault('states', []).append(v)
    elif k == 'FieldMaxLength':
        cur['maxlen'] = v

def full(o):
    parts = []
    while o is not None:
        if '/T' in o: parts.append(str(o['/T']))
        o = o.get('/Parent'); o = o.get_object() if o is not None else None
    return '.'.join(reversed(parts))

r = PdfReader(pdf)
pos = {}
for pno, page in enumerate(r.pages, 1):
    for a in page.get('/Annots') or []:
        a = a.get_object()
        if a.get('/Subtype') != '/Widget': continue
        n = full(a)
        if n and n not in pos:
            rect = [float(x) for x in a['/Rect']]
            o = a; tu = ''
            while o is not None and not tu:
                tu = str(o.get('/TU', '')); o = o.get('/Parent'); o = o.get_object() if o is not None else None
            pos[n] = (pno, round(rect[0]), round(rect[1]), tu.replace('\t', ' ').replace('\n', ' '))

ftmap = {'Text': '/Tx', 'Button': '/Btn', 'Choice': '/Ch'}
rows = {d: [] for d in DOCS}
for n, f in fields.items():
    if n not in pos: print('no widget:', n); continue
    page, x, y, tu = pos[n]
    ft = ftmap.get(f.get('type', ''), f.get('type', ''))
    if ft == 'Push': continue
    doc = next(d for d, pr in DOCS.items() if page in pr)
    states = f['states'] if ft in ('/Btn', '/Ch') else ['']
    for s in states or ['']:
        rows[doc].append((page, ft, n, s, f.get('maxlen', ''), x, y, tu))
for d, rs in rows.items():
    rs.sort(key=lambda t: (t[0], -t[6], t[5]))
    with open(f'{work}/fields_{d}.tsv', 'w') as fh:
        for t in rs: fh.write('\t'.join(map(str, t)) + '\n')
    print(d, len({t[2] for t in rs}), 'fields')
