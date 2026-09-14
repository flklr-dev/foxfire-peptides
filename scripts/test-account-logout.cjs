// Isolated browser-event tests: no customer session or logout request is made.
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const source = fs.readFileSync(path.join(root, 'wp-content/themes/foxfire-child/assets/js/account.js'), 'utf8');
let click;
const listeners = {};
const redirects = [];
const classes = new Set();
let focusCount = 0;
const dialog = {
  open: false, returnValue: '',
  showModal() { this.open = true; },
  close(value) { this.returnValue = value; this.open = false; listeners.close(); },
  addEventListener(type, callback) { listeners[type] = callback; },
  getBoundingClientRect() { return { left: 100, right: 460, top: 100, bottom: 280 }; }
};
const document = { getElementById: () => dialog, body: { classList: { add: v => classes.add(v), remove: v => classes.delete(v) } } };
const buttons = [0, 1].map(() => ({ focus() { document.activeElement = this; } }));
dialog.querySelectorAll = () => buttons;
const jquery = () => ({
  ready: fn => fn(),
  on: (type, selector, callback) => { if (typeof selector === 'string' && selector.includes('customer-logout')) click = callback; },
  length: 0, each() {}
});
vm.runInNewContext(source, { document, jQuery: jquery, window: { location: { assign: v => redirects.push(v) } } });
const signedUrl = 'http://localhost:8080/my-account/customer-logout/?_wpnonce=test-only';
const trigger = { href: signedUrl, focus: () => focusCount++ };
const open = () => {
  let prevented = false;
  click.call(trigger, { preventDefault: () => { prevented = true; } });
  assert.ok(prevented && dialog.open && classes.has('ff-logout-modal-open'));
};
open();
assert.equal(redirects.length, 0);
document.activeElement = buttons[0];
listeners.keydown({ key: 'Tab', shiftKey: true, preventDefault() {} });
assert.equal(document.activeElement, buttons[1]);
listeners.keydown({ key: 'Tab', shiftKey: false, preventDefault() {} });
assert.equal(document.activeElement, buttons[0]);
dialog.close('cancel');
assert.equal(redirects.length, 0);
assert.equal(focusCount, 1);
assert.ok(!classes.has('ff-logout-modal-open'));
open();
listeners.cancel(); // Native Escape fires cancel, then close.
dialog.open = false; listeners.close();
assert.equal(redirects.length, 0);
open();
listeners.click({ target: dialog, clientX: 120, clientY: 120 });
assert.ok(dialog.open); // Dialog padding is not the backdrop.
listeners.click({ target: dialog, clientX: 0, clientY: 0 });
assert.ok(!dialog.open);
assert.equal(redirects.length, 0);
open();
dialog.close('confirm');
assert.deepEqual(redirects, [signedUrl]);
listeners.close(); // Duplicate events must not issue another request.
assert.equal(redirects.length, 1);
console.log('PASS: Open, Cancel, Escape, backdrop dismissal, focus restoration, scroll unlock, signed confirmation URL and duplicate-event protection.');
