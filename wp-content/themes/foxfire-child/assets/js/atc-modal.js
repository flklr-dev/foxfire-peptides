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

			// Optional tap to dismiss toast immediately
			$(document).on('click', '#ff-atc-toast', function () {
				self.hide();
			});

			var isSubmitting = false;

			// Prevent native form submission unconditionally on PDP
			$(document).on('submit', 'form.cart', function (e) {
				e.preventDefault();
				e.stopPropagation();
			});

			// 1. Single Product Page (PDP) AJAX Add-to-Cart Click Interception
			$(document).on('click', '.single-product .single_add_to_cart_button, form.cart .single_add_to_cart_button', function (e) {
				e.preventDefault();
				e.stopPropagation();

				var $btn = $(this);
				var $form = $btn.closest('form.cart');

				if (!$form.length || isSubmitting) {
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

				isSubmitting = true;
				$btn.addClass('is-loading');

				var formData = $form.serializeArray();
				var postData = {
					action: 'foxfire_ajax_add_to_cart_pdp',
					nonce: (window.foxfire_atc_params && window.foxfire_atc_params.nonce) ? window.foxfire_atc_params.nonce : '',
				};

				$.each(formData, function (i, field) {
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
							// Update header counter and fragments
							if (response.data.fragments) {
								$.each(response.data.fragments, function (key, value) {
									$(key).replaceWith(value);
								});
								$(document.body).trigger('wc_fragments_refreshed');
							}
							self.show();
						} else {
							var msg = (response && response.data && response.data.message) ? response.data.message : 'Error adding to cart.';
							alert(msg);
						}
					},
					error: function () {
						alert('Could not add item to cart. Please refresh and try again.');
					},
					complete: function () {
						isSubmitting = false;
						$btn.removeClass('is-loading');
					}
				});
			});

			// 2. Shop & Archive Loop Cards Add-to-Cart Interception
			$(document).on('click', '.ajax_add_to_cart', function () {
				var $btn = $(this);
				$btn.addClass('is-loading');
			});

			$(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
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
