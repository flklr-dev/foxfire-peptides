"""Read-only comparison of published localhost policies against client source files."""
import re
from pathlib import Path
from html.parser import HTMLParser
from urllib.request import urlopen

ROOT = Path('C:/Users/Acer/.codex/attachments')
POLICIES = {
    'terms': ('289dad63-baef-416e-8004-ca182195ea1a', 'terms-and-conditions', 17, 'info'),
    'privacy': ('974d3d67-c086-46b2-b3d2-698b9ea2338e', 'privacy-policy', 10, 'info'),
    'refund': ('ca0d03c4-9ad6-42ed-a1dd-31d53002c6ec', 'refund-and-returns-policy', 12, 'info'),
    'shipping': ('07aa0680-899d-44fb-b881-2bbd754a945b', 'shipping-policy', 12, 'info'),
}

def normalized(text):
    return re.sub(r'\s+', ' ', text.replace('●', '').replace('•', '')).strip()

class PolicyHTML(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.sections, self.toc, self.emails, self.visible = [], [], [], []
        self.main = self.in_toc = self.in_heading = False
        self.current = None
    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'main': self.main = True
        if not self.main: return
        if tag == 'nav' and 'ff-policy-toc' in attrs.get('class', '').split(): self.in_toc = True
        if tag == 'section' and 'ff-policy-section' in attrs.get('class', '').split():
            self.current = {'id': attrs['id'], 'heading': [], 'body': []}
            self.sections.append(self.current)
        if tag == 'h2': self.in_heading = True
        if tag == 'a':
            href = attrs.get('href', '')
            if self.in_toc: self.toc.append(href)
            if href.startswith('mailto:'): self.emails.append(href)
    def handle_endtag(self, tag):
        if tag in ('p', 'li') and self.current is not None: self.current['body'].append(' ')
        if tag == 'section': self.current = None
        if tag == 'h2': self.in_heading = False
        if tag == 'nav': self.in_toc = False
        if tag == 'main': self.main = False
    def handle_data(self, text):
        if not self.main: return
        self.visible.append(text)
        if self.current is not None:
            self.current['heading' if self.in_heading else 'body'].append(text)

checks = 0
def check(condition, label):
    global checks
    assert condition, label
    checks += 1
    print('PASS:', label)

for key, (attachment, slug, count, email) in POLICIES.items():
    source = (ROOT / attachment / 'pasted-text.txt').read_text(encoding='utf-8-sig')
    # User-approved correction supersedes the Shipping attachment's old address only.
    if key == 'shipping': source = source.replace('support@foxfirepeptides.com', 'info@foxfirepeptides.com')
    if key == 'privacy': source = source.split('Developer Note — Not for Publication')[0]
    if key == 'terms': source = source.split('CHECKOUT ACKNOWLEDGEMENT')[0]
    # The client's September 16 age revision supersedes only the previous 18+
    # sentences in the supplied Terms and Privacy source files.
    if key == 'terms':
        source = source.replace(
            'You must be at least 18 years of age',
            'You must be at least 21 years of age',
        )
    if key == 'privacy':
        source = source.replace(
            'individuals 18 years of age or older',
            'individuals 21 years of age or older',
        ).replace('individuals under 18', 'individuals under 21').replace(
            'individual under 18',
            'individual under 21',
        )
    headings = list(re.finditer(r'^([0-9]+)\. (.+)$', source, flags=re.M))
    parser = PolicyHTML()
    with urlopen('http://localhost:8080/' + slug + '/', timeout=30) as response:
        check(response.status == 200, key + ' public page responds successfully')
        parser.feed(response.read().decode('utf-8'))
    check(len(parser.sections) == len(headings) == count, key + ' expected numbered section count')
    for i, (heading, actual) in enumerate(zip(headings, parser.sections)):
        end = headings[i + 1].start() if i + 1 < len(headings) else len(source)
        expected = source[heading.end():end].strip()
        if key == 'terms':
            if i == 15:
                expected = expected.split('Then renumber the current “Changes to Terms” section from #17 to #16 and use:')[1].split('Current #16 Governing Law')[0]
            else:
                expected = expected.split('Replace with:', 1)[1]
                expected = expected.split('Please ')[0].split('For now, please ')[0]
        check(normalized(' '.join(actual['heading'])) == normalized(heading.group(0)), key + ' section ' + str(i + 1) + ' heading')
        actual_body = normalized(''.join(actual['body']))
        expected_body = normalized(expected)
        if actual_body != expected_body:
            print('EXPECTED:', expected_body)
            print('ACTUAL:  ', actual_body)
        check(actual_body == expected_body, key + ' section ' + str(i + 1) + ' exact client wording')
    check(parser.toc == ['#' + section['id'] for section in parser.sections], key + ' table of contents matches every section')
    check(len(set(section['id'] for section in parser.sections)) == count, key + ' anchors are unique')
    check(set(parser.emails) == {'mailto:' + email + '@foxfirepeptides.com'}, key + ' correct client email and clickable mailto')
    text = ' '.join(parser.visible)
    check(not re.search(r'CLIENT TO CONFIRM|ATTORNEY TO PROVIDE|Developer Note|Working draft|Replace with:|Governing Law|Foxfire Peptides LLC|\bWise\b|\bZelle\b|\bGCash\b', text, re.I), key + ' no internal revision notes or removed terms')

print('Success:', checks, 'read-only legal source and public-page checks passed.')
