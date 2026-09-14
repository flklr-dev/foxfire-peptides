import subprocess
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Step 1: Fetch contact page to extract nonce and form time
contact_url = 'http://localhost:8080/contact/'
html = subprocess.check_output(['curl.exe', '-s', contact_url], encoding='utf-8', errors='ignore')

nonce_match = re.search(r'name="foxfire_contact_nonce"\s+value="([a-f0-9]+)"', html)
nonce = nonce_match.group(1) if nonce_match else None
print("Found Nonce:", nonce)

# Step 2: Submit valid form
post_data = [
    '-d', f'foxfire_contact_nonce={nonce}',
    '-d', 'foxfire_contact_submit=1',
    '-d', 'ff_form_time=1000000000',  # Old timestamp to bypass 2s bot check
    '-d', 'ff_website_hp=',
    '-d', 'ff_name=Dr. Jane Smith',
    '-d', 'ff_email=jane.smith@research-lab.org',
    '-d', 'ff_order_number=1045',
    '-d', 'ff_subject=coa',
    '-d', 'ff_message=Requesting COA methodology confirmation for Lot FF-RT2601.'
]

resp = subprocess.check_output(['curl.exe', '-s', '-X', 'POST'] + post_data + [contact_url], encoding='utf-8', errors='ignore')

has_success = "Thank you! Your message has been sent." in resp
print("Contact Form Submit Result - Success Notice Present:", has_success)

# Step 3: Test honeypot trigger (bot)
post_data_hp = [
    '-d', f'foxfire_contact_nonce={nonce}',
    '-d', 'foxfire_contact_submit=1',
    '-d', 'ff_form_time=1000000000',
    '-d', 'ff_website_hp=iamabot',
    '-d', 'ff_name=Spam Bot',
    '-d', 'ff_email=spam@bot.com',
    '-d', 'ff_subject=general',
    '-d', 'ff_message=Spam message'
]
resp_hp = subprocess.check_output(['curl.exe', '-s', '-X', 'POST'] + post_data_hp + [contact_url], encoding='utf-8', errors='ignore')
has_hp_handled = "Thank you! Your message has been received" in resp_hp
print("Honeypot Trap Result - Handled Cleanly:", has_hp_handled)
