/**
 * Checkout enhancements — Chunk 1K.
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var FoxfireCheckout = {
		init: function () {
			this.bindEvents();
			this.styleActivePayment();
		},

		bindEvents: function () {
			var self = this;

			// Highlight active payment method on radio change
			$(document).on('change', 'input[name="payment_method"]', function () {
				self.styleActivePayment();
			});

			// Re-style active payment after WooCommerce AJAX update_checkout
			$(document.body).on('updated_checkout', function () {
				self.styleActivePayment();
			});

			// Add focus classes to custom field wrappers
			$(document).on('focus', '.ff-field-wrap input, .ff-field-wrap select, .ff-field-wrap textarea', function () {
				$(this).closest('.ff-field-wrap').addClass('is-focused');
			});

			$(document).on('blur', '.ff-field-wrap input, .ff-field-wrap select, .ff-field-wrap textarea', function () {
				$(this).closest('.ff-field-wrap').removeClass('is-focused');
			});

			// Button loading state on submit
			$('form.checkout').on('checkout_place_order', function () {
				var $btn = $('#place_order');
				if ($btn.length && !$btn.hasClass('is-loading')) {
					$btn.addClass('is-loading').prop('disabled', true);
				}
			});

			$(document.body).on('checkout_error', function () {
				var $btn = $('#place_order');
				if ($btn.length) {
					$btn.removeClass('is-loading').prop('disabled', false);
				}
			});
		},

		styleActivePayment: function () {
			$('.wc_payment_method').each(function () {
				var $li = $(this);
				var $radio = $li.find('input[name="payment_method"]');
				if ($radio.is(':checked')) {
					$li.addClass('is-selected');
				} else {
					$li.removeClass('is-selected');
				}
			});
		}
	};

	$(document).ready(function () {
		FoxfireCheckout.init();
	});

})(jQuery);
