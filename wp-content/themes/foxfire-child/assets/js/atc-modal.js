/**
 * Foxfire Peptides — Added-to-Cart Center HUD Toast & Button Loading State
 *
 * Handles:
 * - Button loading state with rotating spinner on both PDP and shop cards.
 * - Single Product Page (PDP) AJAX Add-to-Cart for Simple & Variable products.
 * - Black translucent center toast notification with green checkmark (auto-dismisses after 1.8s).
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var AtcToast = {
		$toast: null,
		timer: null,

		init: function () {
			this.$toast = $('#ff-atc-toast');
			this.bindEvents();
		},

		show: function () {
			var self = this;
			if (!this.$toast.length) {
				this.$toast = $('#ff-atc-toast');
			}
			if (!this.$toast.length) {
				return;
			}

			// Clear previous timer if still showing
			if (this.timer) {
				clearTimeout(this.timer);
			}

			this.$toast.addClass('is-active').attr('aria-hidden', 'false');

			// Auto dismiss after 1800ms
			this.timer = setTimeout(function () {
				self.hide();
			}, 1800);
		},

		hide: function () {
			if (this.$toast && this.$toast.length) {
				this.$toast.removeClass('is-active').attr('aria-hidden', 'true');
			}
		},

		bindEvents: function () {
			var self = this;

			// Namespace and replace our delegated handlers so a duplicated/cached
			// script tag cannot register the add-to-cart flow more than once.
			$(document).off('.foxfireAtc');
			$(document.body).off('.foxfireAtc');

			// Optional tap to dismiss toast immediately
			$(document).on('click.foxfireAtc', '#ff-atc-toast', function () {
				self.hide();
			});

			function clearInlineError($form) {
				$form.children('.ff-atc-notice').remove();
			}

			function showInlineError($form, message) {
				clearInlineError($form);

				var $notice = $('<div class="ff-atc-notice ff-atc-notice--error" role="alert" tabindex="-1"><span class="ff-atc-notice__icon" aria-hidden="true">!</span><p class="ff-atc-notice__message"></p></div>');
				$notice.find('.ff-atc-notice__message').text(message);
				$form.prepend($notice);
				$notice.trigger('focus');
			}

			// Prevent native form submission unconditionally on PDP
			$(document).on('submit.foxfireAtc', 'form.cart', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();
			});

			// 1. Single Product Page (PDP) AJAX Add-to-Cart Click Interception
			$(document).on('click.foxfireAtc', '.single-product .single_add_to_cart_button, form.cart .single_add_to_cart_button', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				var $btn = $(this);
				var $form = $btn.closest('form.cart');

				if (!$form.length || $form.data('foxfireAtcSubmitting')) {
					return;
				}

				// Skip if disabled or already loading
				if ($btn.hasClass('is-loading') || $btn.hasClass('disabled') || $btn.is(':disabled')) {
					return;
				}

				// Check variable product selection
				if ($form.hasClass('variations_form')) {
					var variationId = parseInt($form.find('input[name="variation_id"]').val(), 10);
					if (!variationId || variationId === 0) {
						// Variation not selected yet: let WooCommerce prompt
						return;
					}
				}

				clearInlineError($form);
				$form.data('foxfireAtcSubmitting', true);
				$btn.addClass('is-loading');

				var formData = $form.serializeArray();
				var postData = {
					action: 'foxfire_ajax_add_to_cart_pdp',
					nonce: (window.foxfire_atc_params && window.foxfire_atc_params.nonce) ? window.foxfire_atc_params.nonce : '',
				};

				$.each(formData, function (i, field) {
					// WooCommerce treats this hidden field as a native add-to-cart
					// request during wp_loaded. Forwarding it to our custom AJAX
					// endpoint adds the same quantity once there and once here.
					if (field.name === 'add-to-cart') {
						return;
					}
					postData[field.name] = field.value;
				});

				// Fallbacks for product_id
				if (!postData.product_id) {
					var pid = $btn.val() || $form.find('[name="add-to-cart"]').val() || $form.find('input[name="product_id"]').val();
					if (pid) {
						postData.product_id = pid;
					}
				}

				// Fallback for quantity
				if (!postData.quantity) {
					var qtyInput = $form.find('input.qty, input[name="quantity"]').val();
					postData.quantity = qtyInput ? parseInt(qtyInput, 10) : 1;
				}

				$.ajax({
					type: 'POST',
					url: (window.foxfire_atc_params && window.foxfire_atc_params.ajax_url) ? window.foxfire_atc_params.ajax_url : '/wp-admin/admin-ajax.php',
					data: postData,
					dataType: 'json',
					success: function (response) {
						if (response && response.success && response.data) {
							clearInlineError($form);
							// Update header counter and fragments
							if (response.data.fragments) {
								$.each(response.data.fragments, function (key, value) {
									$(key).replaceWith(value);
								});
								$(document.body).trigger('wc_fragments_refreshed');
							}
							self.show();
						} else {
							var msg = (response && response.data && response.data.message) ? response.data.message : ((window.foxfire_atc_params && window.foxfire_atc_params.i18n) ? window.foxfire_atc_params.i18n.error : 'Could not add item to cart.');
							showInlineError($form, msg);
						}
					},
					error: function (xhr) {
						var responseMessage = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '';
						var msg = responseMessage || ((window.foxfire_atc_params && window.foxfire_atc_params.i18n) ? window.foxfire_atc_params.i18n.network_error : 'Could not add item to cart. Please refresh and try again.');
						showInlineError($form, msg);
					},
					complete: function () {
						$form.removeData('foxfireAtcSubmitting');
						$btn.removeClass('is-loading');
					}
				});
			});

			// 2. Shop & Archive Loop Cards Add-to-Cart Interception
			$(document).on('click.foxfireAtc', '.ajax_add_to_cart', function () {
				var $btn = $(this);
				$btn.addClass('is-loading');
			});

			$(document.body).on('added_to_cart.foxfireAtc', function (event, fragments, cart_hash, $button) {
				if ($button && $button.length) {
					$button.removeClass('is-loading');
					$button.siblings('a.added_to_cart').remove();

					// If loop card, show the center HUD toast
					if (!$button.hasClass('single_add_to_cart_button')) {
						self.show();
					}
				}

				// Always clean up any stray "View cart" links
				$('a.added_to_cart.wc-forward').remove();
			});
		}
	};

	$(document).ready(function () {
		AtcToast.init();
	});

})(jQuery);
