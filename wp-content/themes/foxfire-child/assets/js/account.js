/**
 * Customer Account Portal enhancements — Chunk 1L.
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var FoxfireAccount = {
		init: function () {
			this.bindLogoutConfirmation();
			this.bindTabs();
			this.bindPasswordToggles();
			this.bindAddressPairing();
			this.bindOrderMoreToggles();
		},

		bindLogoutConfirmation: function () {
			var dialog = document.getElementById('ff-logout-dialog');
			if (!dialog || typeof dialog.showModal !== 'function') return;
			var logoutUrl = '';
			var trigger = null;

			$(document).on('click', '.ff-account-menu-item--customer-logout .ff-account-menu-link', function (e) {
				if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
				e.preventDefault();
				if (dialog.open) return;
				// Preserve WooCommerce's signed logout URL, including its nonce.
				logoutUrl = this.href;
				trigger = this;
				dialog.returnValue = '';
				dialog.showModal();
				document.body.classList.add('ff-logout-modal-open');
			});
			dialog.addEventListener('close', function () {
				document.body.classList.remove('ff-logout-modal-open');
				var url = logoutUrl;
				logoutUrl = '';
				if (dialog.returnValue === 'confirm' && url) {
					window.location.assign(url);
				} else if (trigger) {
					trigger.focus();
				}
			});
			dialog.addEventListener('cancel', function () {
				// Escape is cancellation, never confirmation.
				dialog.returnValue = 'cancel';
			});
			dialog.addEventListener('keydown', function (e) {
				if (e.key !== 'Tab') return;
				var buttons = dialog.querySelectorAll('button:not([disabled])');
				var first = buttons[0];
				var last = buttons[buttons.length - 1];
				if (e.shiftKey && document.activeElement === first) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			});
			dialog.addEventListener('click', function (e) {
				var bounds = dialog.getBoundingClientRect();
				if (e.target === dialog && (e.clientX < bounds.left || e.clientX > bounds.right || e.clientY < bounds.top || e.clientY > bounds.bottom)) {
					dialog.close('cancel');
				}
			});
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
				// WooCommerce wraps reset inputs in span.password-input at runtime.
				var $input = $btn.closest('.ff-password-input-wrap').find('input').first();

				if ($input.length) {
					if ($input.attr('type') === 'password') {
						$input.attr('type', 'text');
						$btn.attr('aria-label', 'Hide password').attr('aria-pressed', 'true').addClass('is-visible');
					} else {
						$input.attr('type', 'password');
						$btn.attr('aria-label', 'Show password').attr('aria-pressed', 'false').removeClass('is-visible');
					}
				}
			});
		}
	};

	$(document).ready(function () {
		FoxfireAccount.init();
	});

})(jQuery);
