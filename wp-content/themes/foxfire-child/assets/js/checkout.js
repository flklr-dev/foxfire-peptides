/**
 * Checkout enhancements — Shopee-style flow, Address Modal, and payment method styling.
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var FoxfireCheckout = {
		init: function () {
			this.bindEvents();
			this.styleActivePayment();
			this.updateAddressPreview();
			this.updatePlaceOrderStatus();
		},

		bindEvents: function () {
			var self = this;

			// Highlight active payment method on radio change
			$(document).on('change', 'input[name="payment_method"]', function () {
				self.styleActivePayment();
			});

			// Re-style active payment and place order status after WooCommerce AJAX update_checkout
			$(document.body).on('updated_checkout', function () {
				self.styleActivePayment();
				self.updatePlaceOrderStatus();
			});

			// Add focus classes to custom field wrappers
			$(document).on('focus', '.ff-field-wrap input, .ff-field-wrap select, .ff-field-wrap textarea', function () {
				$(this).closest('.ff-field-wrap').addClass('is-focused');
			});

			$(document).on('blur', '.ff-field-wrap input, .ff-field-wrap select, .ff-field-wrap textarea', function () {
				$(this).closest('.ff-field-wrap').removeClass('is-focused');
			});

			// Remove error class on input
			$(document).on('input change', '.has-error', function () {
				if ($(this).val()) {
					$(this).removeClass('has-error');
				}
			});

			// Terms & Conditions checkbox state enables / disables Place Order button
			$(document).on('change', '#terms, input[name="terms"]', function () {
				$('.woocommerce-terms-and-conditions-wrapper').removeClass('has-error');
				self.updatePlaceOrderStatus();
			});

			// Clicking a disabled place order button provides clear guidance
			$(document).on('click', '#place_order.is-disabled, .ff-place-order-btn.is-disabled', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $terms = $('#terms');
				if ($terms.length && !$terms.is(':checked')) {
					$terms.focus();
					var $wrap = $terms.closest('.woocommerce-terms-and-conditions-wrapper');
					$wrap.removeClass('has-error');
					setTimeout(function () {
						$wrap.addClass('has-error');
					}, 10);
				}
				return false;
			});

			// Validation & Button loading state on submit
			$('form.checkout').on('checkout_place_order', function () {
				var $terms = $('#terms');
				if ($terms.length && !$terms.is(':checked')) {
					$terms.focus();
					var $wrap = $terms.closest('.woocommerce-terms-and-conditions-wrapper');
					$wrap.removeClass('has-error');
					setTimeout(function () {
						$wrap.addClass('has-error');
					}, 10);
					return false;
				}

				// Prevent placing order if no delivery address exists yet
				var hasAddress = $('#ffShopeeAddressCard').hasClass('has-saved-address');
				if (!hasAddress) {
					self.openAddressModal();
					return false;
				}

				var $btn = $('#place_order');
				if ($btn.length && !$btn.hasClass('is-loading')) {
					$btn.data('original-html', $btn.html());
					var processingText = (window.foxfire_checkout_params && window.foxfire_checkout_params.i18n_processing)
						? window.foxfire_checkout_params.i18n_processing
						: 'Processing Order...';
					$btn.addClass('is-loading');
					$btn.html('<span class="ff-btn-spinner" aria-hidden="true"></span> ' + processingText);
				}

				// Show the standard UX order processing overlay
				$('#ffCheckoutProcessingOverlay').attr('aria-hidden', 'false').fadeIn(200);
			});

			$(document.body).on('checkout_error', function () {
				// Dismiss the processing overlay on error so customer can fix any field issue
				$('#ffCheckoutProcessingOverlay').attr('aria-hidden', 'true').fadeOut(200);

				var $btn = $('#place_order');
				if ($btn.length) {
					$btn.removeClass('is-loading');
					if ($btn.data('original-html')) {
						$btn.html($btn.data('original-html'));
					}
					self.updatePlaceOrderStatus();
				}
			});

			// Dismiss processing overlay if an AJAX network error occurs during checkout
			$(document).ajaxError(function (event, jqXHR, ajaxSettings) {
				if (ajaxSettings && ajaxSettings.url && ajaxSettings.url.indexOf('wc-ajax=checkout') !== -1) {
					$('#ffCheckoutProcessingOverlay').attr('aria-hidden', 'true').fadeOut(200);
					var $btn = $('#place_order');
					if ($btn.length) {
						$btn.removeClass('is-loading');
						if ($btn.data('original-html')) {
							$btn.html($btn.data('original-html'));
						}
						self.updatePlaceOrderStatus();
					}
				}
			});

			// Open Address Modal when clicking Change or + Set Address button
			$(document).on('click', '#ffAddressChangeBtnDesktop', function (e) {
				e.preventDefault();
				e.stopPropagation();
				self.openAddressModal();
			});

			// Address card click handler:
			// On desktop (web), if the user already has an address, clicking the card does nothing.
			// The user must click the "Change" button.
			$(document).on('click', '#ffShopeeAddressCard', function (e) {
				var isDesktop = window.innerWidth >= 768;
				var hasSavedAddress = $(this).hasClass('has-saved-address');

				if (isDesktop && hasSavedAddress) {
					return;
				}

				e.preventDefault();
				e.stopPropagation();
				self.openAddressModal();
			});

			// Keyboard enter on card opens modal (if allowed)
			$(document).on('keydown', '#ffShopeeAddressCard', function (e) {
				if (e.key === 'Enter' || e.keyCode === 13) {
					var isDesktop = window.innerWidth >= 768;
					var hasSavedAddress = $(this).hasClass('has-saved-address');
					if (isDesktop && hasSavedAddress) {
						return;
					}
					e.preventDefault();
					self.openAddressModal();
				}
			});

			// Close Modal handlers
			$(document).on('click', '#ffAddressModalClose, #ffAddressModalBackdrop', function (e) {
				e.preventDefault();
				self.closeAddressModal();
			});

			// Close on ESC key
			$(document).on('keydown', function (e) {
				if (e.key === 'Escape' || e.keyCode === 27) {
					if ($('#ffAddressModal').hasClass('is-open')) {
						self.closeAddressModal();
					}
				}
			});

			// Save & Use Address from Modal
			$(document).on('click', '#ffAddressModalSave', function (e) {
				e.preventDefault();
				self.saveAndUseAddress();
			});

			// Live update preview card as user inputs fields
			$(document).on('input change', 'input[name^="billing_"], select[name^="billing_"]', function () {
				self.updateAddressPreview();
			});
		},

		updatePlaceOrderStatus: function () {
			var $terms = $('#terms');
			var $btn = $('#place_order');

			if (!$btn.length) {
				return;
			}

			// If terms checkbox is present on page
			if ($terms.length) {
				if ($terms.is(':checked')) {
					$btn.prop('disabled', false).removeClass('is-disabled');
				} else {
					$btn.prop('disabled', true).addClass('is-disabled');
				}
			}
		},

		openAddressModal: function () {
			var $modal = $('#ffAddressModal');
			$modal.addClass('is-open').attr('aria-hidden', 'false');
			$('body').addClass('ff-modal-locked');

			// If select2 was attached by any script, destroy it so native select functions reliably
			if ($.fn.select2) {
				$modal.find('select').each(function () {
					if ($(this).data('select2')) {
						$(this).select2('destroy');
					}
				});
			}

			// Ensure country_to_state handlers populate state options
			var $country = $('#billing_country');
			if ($country.length) {
				$country.trigger('change');
			}

			this.pairAddressFields();

			// Focus first visible input
			setTimeout(function () {
				var $firstInput = $modal.find('input:visible').first();
				if ($firstInput.length) {
					$firstInput.focus();
				}
			}, 100);
		},

		pairAddressFields: function () {
			var $modal = $('#ffAddressModal');
			if (!$modal.length) return;

			// 1. Pair Email and Phone on Row 2
			var $email = $modal.find('#billing_email_field');
			var $phone = $modal.find('#billing_phone_field');
			if ($email.length && $phone.length && $email.next()[0] !== $phone[0]) {
				$email.after($phone);
			}

			// 2. Pair Town / City and Postcode on Row 6
			var $city = $modal.find('#billing_city_field');
			var $postcode = $modal.find('#billing_postcode_field');
			if ($city.length && $postcode.length && $city.next()[0] !== $postcode[0]) {
				$city.after($postcode);
			}

			// 3. Ensure Country and State / Region labels
			var $countryLabel = $modal.find('#billing_country_field label');
			$countryLabel.each(function () {
				var req = $(this).find('.required').prop('outerHTML') || '';
				$(this).html('Country' + (req ? '&nbsp;' + req : ''));
			});

			var $stateLabel = $modal.find('#billing_state_field label');
			$stateLabel.each(function () {
				var req = $(this).find('.required').prop('outerHTML') || '';
				$(this).html('State / Region' + (req ? '&nbsp;' + req : ''));
			});
		},

		closeAddressModal: function () {
			var $modal = $('#ffAddressModal');
			$modal.removeClass('is-open').attr('aria-hidden', 'true');
			$('body').removeClass('ff-modal-locked');
			this.updateAddressPreview();
		},

		saveAndUseAddress: function () {
			var $modal = $('#ffAddressModal');
			var hasErrors = false;
			var $firstInvalid = null;

			// Required fields validation (including Phone)
			var requiredFieldNames = [
				'billing_first_name',
				'billing_last_name',
				'billing_email',
				'billing_phone',
				'billing_country',
				'billing_state',
				'billing_city',
				'billing_postcode',
				'billing_address_1'
			];

			requiredFieldNames.forEach(function (name) {
				var $field = $modal.find('[name="' + name + '"]');
				if ($field.length && !$field.val()) {
					hasErrors = true;
					$field.addClass('has-error');
					if (!$firstInvalid) {
						$firstInvalid = $field;
					}
				} else {
					$field.removeClass('has-error');
				}
			});

			if (hasErrors) {
				if ($firstInvalid) {
					$firstInvalid.focus();
				}
				return;
			}

			// Display whole address preview immediately
			this.updateAddressPreview();
			this.closeAddressModal();

			// Permanently save address directly to user's WordPress account database & customer session via AJAX
			var payload = {
				action: 'foxfire_save_checkout_address',
				nonce: (window.foxfire_checkout_params && window.foxfire_checkout_params.nonce) ? window.foxfire_checkout_params.nonce : '',
				billing_first_name: $('input[name="billing_first_name"]').val() || '',
				billing_last_name:  $('input[name="billing_last_name"]').val() || '',
				billing_email:      $('input[name="billing_email"]').val() || '',
				billing_phone:      $('input[name="billing_phone"]').val() || '',
				billing_country:    $('select[name="billing_country"]').val() || '',
				billing_state:      $('select[name="billing_state"]').val() || $('input[name="billing_state"]').val() || '',
				billing_city:       $('input[name="billing_city"]').val() || '',
				billing_postcode:   $('input[name="billing_postcode"]').val() || '',
				billing_address_1:  $('input[name="billing_address_1"]').val() || '',
				billing_address_2:  $('input[name="billing_address_2"]').val() || '',
				set_default:        $('#ff_set_default_address').is(':checked') ? 1 : 0
			};

			var ajaxUrl = (window.foxfire_checkout_params && window.foxfire_checkout_params.ajax_url)
				? window.foxfire_checkout_params.ajax_url
				: '/wp-admin/admin-ajax.php';

			$.post(ajaxUrl, payload);

			// Trigger WooCommerce recalculation
			$(document.body).trigger('update_checkout');
		},

		updateAddressPreview: function () {
			var firstName = $('input[name="billing_first_name"]').val() || '';
			var lastName  = $('input[name="billing_last_name"]').val() || '';
			var phone     = $('input[name="billing_phone"]').val() || '';
			var address1  = $('input[name="billing_address_1"]').val() || '';
			var address2  = $('input[name="billing_address_2"]').val() || '';
			var city      = $('input[name="billing_city"]').val() || '';
			var $stateSel = $('select[name="billing_state"]');
			var stateText = ($stateSel.length && $stateSel.find('option:selected').val())
				? $stateSel.find('option:selected').text()
				: ($('input[name="billing_state"]').val() || '');
			var postcode  = $('input[name="billing_postcode"]').val() || '';
			var $countrySel = $('select[name="billing_country"]');
			var countryText = ($countrySel.length && $countrySel.find('option:selected').val())
				? $countrySel.find('option:selected').text()
				: '';

			var fullName = $.trim(firstName + ' ' + lastName);
			var addressParts = [];
			if (address1) addressParts.push(address1);
			if (address2) addressParts.push(address2);
			if (city) addressParts.push(city);
			if (stateText || postcode) addressParts.push($.trim(stateText + ' ' + postcode));
			if (countryText) addressParts.push(countryText);

			var fullAddress = addressParts.join(', ');

			var $card = $('#ffShopeeAddressCard');
			var $btnDesktop = $('#ffAddressChangeBtnDesktop');

			if (fullName && address1) {
				$card.removeClass('no-saved-address').addClass('has-saved-address');
				$btnDesktop.text('Change');

				// Desktop Preview (Whole address)
				$('#ffPreviewName').text(fullName);
				if (phone) {
					$('#ffPreviewPhone').text(phone).show();
				} else {
					$('#ffPreviewPhone').hide();
				}
				$('#ffPreviewAddress').text(fullAddress);
				$('#ffDesktopAddressLine').css('display', 'flex');
				$('#ffDesktopAddressEmpty').hide();

				// Mobile Preview (Whole address)
				$('#ffMobilePreviewName').text(fullName);
				if (phone) {
					$('#ffMobilePreviewPhone').text(phone).show();
				} else {
					$('#ffMobilePreviewPhone').hide();
				}
				$('#ffMobilePreviewAddress').text(fullAddress);
			} else {
				$card.removeClass('has-saved-address').addClass('no-saved-address');
				$btnDesktop.text('+ Set Address');

				// Desktop Preview
				$('#ffDesktopAddressLine').hide();
				$('#ffDesktopAddressEmpty').show();

				// Mobile Preview
				$('#ffMobilePreviewName').text('Set delivery address');
				$('#ffMobilePreviewPhone').hide();
				$('#ffMobilePreviewAddress').text('Click to enter your delivery and contact details');
			}
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
