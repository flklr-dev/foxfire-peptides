/**
 * Foxfire Peptides — Cart Drawer Interactivity & AJAX Mini-Cart
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var CartDrawer = {
		init: function () {
			this.bindEvents();
		},

		getDrawer: function () {
			return $('#ff-cart-drawer');
		},

		getOverlay: function () {
			return $('#ff-cart-drawer-overlay');
		},

		isOpen: function () {
			return this.getDrawer().hasClass('is-active');
		},

		open: function () {
			var $drawer = this.getDrawer();
			var $overlay = this.getOverlay();

			if ($drawer.length) {
				$drawer.addClass('is-active').attr('aria-hidden', 'false');
			}
			if ($overlay.length) {
				$overlay.addClass('is-active').attr('aria-hidden', 'false');
			}
			$('body').addClass('ff-drawer-open');
		},

		close: function () {
			var $drawer = this.getDrawer();
			var $overlay = this.getOverlay();

			if ($drawer.length) {
				$drawer.removeClass('is-active').attr('aria-hidden', 'true');
			}
			if ($overlay.length) {
				$overlay.removeClass('is-active').attr('aria-hidden', 'true');
			}
			$('body').removeClass('ff-drawer-open');
		},

		bindEvents: function () {
			var self = this;

			// Close buttons & overlay click
			$(document).on('click', '[data-ff-drawer-close], #ff-cart-drawer-overlay', function (e) {
				e.preventDefault();
				self.close();
			});

			// Close on ESC
			$(document).on('keydown', function (e) {
				if (e.key === 'Escape' && self.isOpen()) {
					self.close();
				}
			});

			// Open drawer automatically when item is added to cart
			$(document.body).on('added_to_cart', function () {
				self.refresh(true);
			});

			// When WooCommerce fragments refresh, keep drawer open if it was open
			$(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
				if ($('body').hasClass('ff-drawer-open')) {
					self.open();
				}
			});

			// Drawer quantity stepper clicks (AJAX)
			$(document).on('click', '.ff-drawer-stepper__btn', function (e) {
				e.preventDefault();
				var $btn = $(this);
				var $item = $btn.closest('.ff-drawer-item');
				var cartKey = $item.data('cart-key');
				var action = $btn.data('action');
				var $val = $item.find('.ff-drawer-stepper__val');
				var currentQty = parseInt($val.text(), 10) || 1;
				var newQty = action === 'plus' ? currentQty + 1 : Math.max(0, currentQty - 1);

				self.updateItemQty(cartKey, newQty);
			});

			// Drawer remove item click (AJAX)
			$(document).on('click', '.ff-drawer-item__remove', function (e) {
				e.preventDefault();
				var cartKey = $(this).data('cart-key');
				self.removeItem(cartKey);
			});
		},

		refresh: function (autoOpen) {
			var self = this;
			$(document.body).trigger('wc_fragment_refresh');

			if (autoOpen) {
				setTimeout(function () {
					self.open();
				}, 150);
			}
		},

		updateItemQty: function (cartKey, qty) {
			var self = this;
			var $drawer = this.getDrawer();
			var $body = $drawer.find('.ff-cart-drawer__body');
			$body.addClass('is-loading');

			$.ajax({
				type: 'POST',
				url: wc_cart_drawer_params.ajax_url,
				data: {
					action: 'foxfire_update_drawer_item',
					nonce: wc_cart_drawer_params.nonce,
					cart_key: cartKey,
					quantity: qty
				},
				success: function (response) {
					if (response && response.success && response.data) {
						self.replaceContent(response.data.drawer_html);
						self.updateBadge(response.data.cart_count);
					} else {
						self.refresh(false);
					}
				},
				complete: function () {
					$body.removeClass('is-loading');
				}
			});
		},

		removeItem: function (cartKey) {
			var self = this;
			var $drawer = this.getDrawer();
			var $body = $drawer.find('.ff-cart-drawer__body');
			$body.addClass('is-loading');

			$.ajax({
				type: 'POST',
				url: wc_cart_drawer_params.ajax_url,
				data: {
					action: 'foxfire_remove_drawer_item',
					nonce: wc_cart_drawer_params.nonce,
					cart_key: cartKey
				},
				success: function (response) {
					if (response && response.success && response.data) {
						self.replaceContent(response.data.drawer_html);
						self.updateBadge(response.data.cart_count);
					} else {
						self.refresh(false);
					}
				},
				complete: function () {
					$body.removeClass('is-loading');
				}
			});
		},

		replaceContent: function (html) {
			if (!html) return;
			var $drawer = this.getDrawer();
			var $newDrawer = $(html);
			if ($newDrawer.is('#ff-cart-drawer')) {
				$drawer.html($newDrawer.html());
			} else {
				var $inner = $newDrawer.find('.ff-cart-drawer__inner');
				if ($inner.length) {
					$drawer.find('.ff-cart-drawer__inner').replaceWith($inner);
				} else {
					$drawer.html(html);
				}
			}
		},

		updateBadge: function (count) {
			var $badges = $('.ff-header-cart-count, .ff-cart-drawer__count-badge');
			$badges.text(count);
			if (count > 0) {
				$('.ff-header-cart-count').show().removeClass('ff-header-cart-count--empty');
			}
		}
	};

	$(document).ready(function () {
		CartDrawer.init();
	});

})(jQuery);
