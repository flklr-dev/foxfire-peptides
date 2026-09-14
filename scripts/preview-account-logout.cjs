// Temporary local-only UI fixture. Uses actual theme assets; no customer login.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const root = path.resolve(__dirname, '..', 'wp-content/themes/foxfire-child');
const template = fs.readFileSync(path.join(root, 'woocommerce/myaccount/navigation.php'), 'utf8');
const dialog = template.match(/<dialog[\s\S]*?<\/dialog>/)[0].replace(/<\?php esc_html_e\( '([^']+)', 'foxfire-child' \); \?>/g, '$1');
const css = fs.readFileSync(path.join(root, 'assets/css/woocommerce.css'), 'utf8');
const shell = fs.readFileSync(path.join(root, 'assets/css/shell.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/account.js'), 'utf8');
const html = `<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><title>Local logout UI test</title><style>${shell}\n${css}\nbody{font-family:Arial,sans-serif;padding:32px;box-sizing:border-box}body *{box-sizing:border-box}</style></head><body class="woocommerce-account"><h1>Local logout UI test</h1><a class="ff-header-account-btn is-active" href="#account" aria-label="My Account">Account</a><nav><ul><li class="ff-account-menu-item--customer-logout"><a class="ff-account-menu-link" href="#confirmed-logout">Logout</a></li></ul></nav>${dialog}<script src="http://localhost:8080/wp-includes/js/jquery/jquery.min.js"></script><script>${js}</script></body></html>`;
http.createServer((req, res) => { res.writeHead(200, { 'Content-Type': 'text/html', 'Cache-Control': 'no-store' }); res.end(html); }).listen(8086, '127.0.0.1', () => console.log('Logout UI fixture: http://127.0.0.1:8086/ (no real session/logout)'));
