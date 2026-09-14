// Isolated checkout JavaScript unit test; no browser, requests, accounts or orders.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(path.resolve(__dirname, '../../../themes/foxfire-child/assets/js/checkout.js'), 'utf8');
const handlers = new Map();
const document = { body: {} };
const button = {
  length: 1, review: '1', disabled: false, classes: new Set(),
  attr() { return this.review; },
  prop(key, value) { assert.equal(key, 'disabled'); this.disabled = value; return this; },
  addClass(name) { this.classes.add(name); return this; },
  removeClass(name) { this.classes.delete(name); return this; },
};
const terms = { length: 1, checked: true, is() { return this.checked; } };
const generic = {
  on(event, selector, callback) { handlers.set(event, callback || selector); return this; },
  ajaxError(callback) { handlers.set('ajaxError', callback); return this; },
  ready(callback) { callback(); },
};
const $ = selector => selector === '#place_order' ? button : selector === '#terms' ? terms : generic;
const context = { jQuery: $, document, window: { navigator: { onLine: true } } };
vm.createContext(context);
vm.runInContext(source.replace('FoxfireCheckout.init();', 'globalThis.subject = FoxfireCheckout;'), context);
context.subject.updatePlaceOrderStatus();
assert.equal(button.disabled, true);
assert.equal(button.classes.has('is-disabled'), true);
console.log('PASS: Review Place Order stays disabled even with terms checked');
context.subject.bindEvents();
context.subject.styleActivePayment = () => {};
button.disabled = false;
handlers.get('updated_checkout')();
assert.equal(button.disabled, true);
console.log('PASS: WooCommerce AJAX refresh cannot re-enable review ordering');
assert.equal(handlers.get('checkout_place_order')(), false);
console.log('PASS: Review submission event returns false before processing UI');
button.review = null;
context.subject.updatePlaceOrderStatus();
assert.equal(button.disabled, false);
terms.checked = false;
context.subject.updatePlaceOrderStatus();
assert.equal(button.disabled, true);
console.log('PASS: Outside review, native terms checked/unchecked button behavior remains intact');
