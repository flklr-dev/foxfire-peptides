/**
 * Customer Account Portal enhancements — Chunk 1L.
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var FoxfireAccount = {
		init: function () {
			this.bindTabs();
			this.bindPasswordToggles();
			this.bindAddressPairing();
			this.bindOrderMoreToggles();
		},

		bindOrderMoreToggles: function () {
			$(document).on('click', '.ff-order-more-toggle', function (e) {
				e.preventDefault();
				var $btn = $(this);
				var targetId = $btn.attr('aria-controls');
				var $container = $('#' + targetId);
				var isExpanded = $btn.attr('aria-expanded') === 'true';

				if (isExpanded) {
					$container.slideUp(180, function () {
						$container.attr('hidden', true);
					});
					$btn.attr('aria-expanded', 'false');
					$btn.find('.ff-order-more-label').text($btn.data('more-text'));
				} else {
					$container.removeAttr('hidden').hide().slideDown(180);
					$btn.attr('aria-expanded', 'true');
					$btn.find('.ff-order-more-label').text($btn.data('less-text'));
				}
			});
		},

		bindAddressPairing: function () {
			function pairFields() {
				// 1. Strictly pair Email and Phone in Row 2
				var $billingEmail = $('#billing_email_field');
				var $billingPhone = $('#billing_phone_field');
				if ($billingEmail.length && $billingPhone.length && $billingEmail.next()[0] !== $billingPhone[0]) {
					$billingEmail.after($billingPhone);
				}

				// 2. Strictly pair Town / City and Postcode in Row 6
				var $billingCity = $('#billing_city_field');
				var $billingPostcode = $('#billing_postcode_field');
				if ($billingCity.length && $billingPostcode.length && $billingCity.next()[0] !== $billingPostcode[0]) {
					$billingCity.after($billingPostcode);
				}

				var $shippingCity = $('#shipping_city_field');
				var $shippingPostcode = $('#shipping_postcode_field');
				if ($shippingCity.length && $shippingPostcode.length && $shippingCity.next()[0] !== $shippingPostcode[0]) {
					$shippingCity.after($shippingPostcode);
				}

				// 3. Ensure labels always say "Country" and "State / Region"
				var $countryLabel = $('#billing_country_field label, #shipping_country_field label');
				$countryLabel.each(function () {
					var $lbl = $(this);
					var req = $lbl.find('.required').prop('outerHTML') || '';
					$lbl.html('Country' + (req ? '&nbsp;' + req : ''));
				});

				var $stateLabel = $('#billing_state_field label, #shipping_state_field label');
				$stateLabel.each(function () {
					var $lbl = $(this);
					var req = $lbl.find('.required').prop('outerHTML') || '';
					$lbl.html('State / Region' + (req ? '&nbsp;' + req : ''));
				});
			}

			pairFields();
			$(document).on('wc_address_i18n_ready country_to_state_changed updated_checkout', function () {
				setTimeout(pairFields, 20);
			});
		},

		bindTabs: function () {
			$(document).on('click', '.ff-auth-tab-btn', function (e) {
				e.preventDefault();
				var target = $(this).data('tab');

				$('.ff-auth-tab-btn').removeClass('is-active').attr('aria-selected', 'false');
				$(this).addClass('is-active').attr('aria-selected', 'true');

				$('.ff-auth-panel').removeClass('is-active').hide();
				$('#' + target).addClass('is-active').fadeIn(150);
			});
		},

		bindPasswordToggles: function () {
			$(document).on('click', '.ff-pwd-toggle', function (e) {
				e.preventDefault();
				var $btn = $(this);
				var $input = $btn.siblings('input');

				if ($input.length) {
					if ($input.attr('type') === 'password') {
						$input.attr('type', 'text');
						$btn.attr('aria-label', 'Hide password').addClass('is-visible');
					} else {
						$input.attr('type', 'password');
						$btn.attr('aria-label', 'Show password').removeClass('is-visible');
					}
				}
			});
		}
	};

	$(document).ready(function () {
		FoxfireAccount.init();
	});

})(jQuery);
