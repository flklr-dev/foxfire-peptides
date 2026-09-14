// Isolated actual-template visibility test. No reset key or password change.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const root = path.resolve(__dirname, '..', 'wp-content/themes/foxfire-child');
const template = fs.readFileSync(path.join(root, 'woocommerce/myaccount/form-reset-password.php'), 'utf8');
const form = template.match(/<form[\s\S]*?<\/form>/)[0]
  .replace(/<\?php esc_(?:html|attr)_e\( '([^']+)', 'foxfire-child' \); \?>/g, '$1')
  .replace(/<\?php[\s\S]*?\?>/g, '');
const css = fs.readFileSync(path.join(root, 'assets/css/woocommerce.css'), 'utf8');
const js = fs.readFileSync(path.join(root, 'assets/js/account.js'), 'utf8');
const html = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Local password visibility test</title><style>${css} body{font-family:Arial,sans-serif;padding:24px}.ff-auth-card{max-width:420px;margin:auto}</style></head><body class="woocommerce-account"><main class="woocommerce"><div class="ff-auth-card"><h1>Local visibility test</h1>${form}</div></main><script src="http://localhost:8080/wp-includes/js/jquery/jquery.min.js"></script><script src="http://localhost:8080/wp-content/plugins/woocommerce/assets/js/js-cookie/js.cookie.min.js"></script><script>var woocommerce_params={i18n_password_show:'Show password',i18n_password_hide:'Hide password'};</script><script src="http://localhost:8080/wp-content/plugins/woocommerce/assets/js/frontend/woocommerce.js"></script><script>${js}</script></body></html>`;
http.createServer((req, res) => {res.writeHead(200,{'Content-Type':'text/html','Cache-Control':'no-store'});res.end(html);}).listen(8086,'127.0.0.1',()=>console.log('Visibility-only fixture: http://127.0.0.1:8086/'));
