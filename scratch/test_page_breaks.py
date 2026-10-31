import docx

doc = docx.Document('c:/xampp/htdocs/Kravyo/Kravyo_Project_Report.docx')

breaks_before_ch1 = 0
for p in doc.paragraphs:
    if 'Chapter 1:' in p.text:
        break
    if 'type="page"' in p._p.xml:
        breaks_before_ch1 += 1

print('Explicit page breaks before Chapter 1:', breaks_before_ch1)

# Check all chapters and page breaks
ch_breaks = {}
current_ch = 'Prelim'
counts = {current_ch: 0}
for p in doc.paragraphs:
    for ch_title in ['Chapter 1:', 'Chapter 2:', 'Chapter 3:', 'Chapter 4:', 'Chapter 5:', 'Chapter 6:', 'References']:
        if ch_title in p.text:
            current_ch = ch_title
            counts[current_ch] = 0
    if 'type="page"' in p._p.xml:
        counts[current_ch] = counts.get(current_ch, 0) + 1

for k, v in counts.items():
    print(f'{k}: {v} page breaks')
