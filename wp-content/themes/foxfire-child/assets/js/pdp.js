/**
 * Foxfire Peptides — Product Detail Page (PDP) Interactions (Chunk 1G).
 *
 * Handles:
 * - 1 / 3 / 5 Vial quantity tier pill selection and dynamic price updates.
 * - Single-vial stock deduction quantity sync with form input.
 * - Mobile sticky Add to Cart bar visibility.
 * - Dynamic price sync with WooCommerce variable product dropdowns.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var stickyBar = document.getElementById('ff-sticky-atc');
		var mainForm = document.querySelector('form.cart');
		var mainButton = document.querySelector('form.cart .single_add_to_cart_button');
		var qtyInput = document.querySelector('form.cart input.qty');
		var tierContainer = document.querySelector('.ff-tier-selector');

		// 1. Quantity Tier Buttons Interaction
		if (tierContainer && mainForm) {
			var tierButtons = tierContainer.querySelectorAll('.ff-tier-btn');

			tierButtons.forEach(function (btn) {
				btn.addEventListener('click', function (e) {
					e.preventDefault();

					var qty = parseInt(btn.getAttribute('data-qty'), 10) || 1;

					// Update active class
					tierButtons.forEach(function (b) {
						b.classList.remove('is-active');
					});
					btn.classList.add('is-active');

					// Update WooCommerce quantity input
					if (qtyInput) {
						qtyInput.value = qty;
						// Trigger change event for any WC listeners
						var event = new Event('change', { bubbles: true });
						qtyInput.dispatchEvent(event);
					}
				});
			});
		}

		// 2. Mobile Sticky Add to Cart
		if (stickyBar && mainButton) {
			var stickyTrigger = stickyBar.querySelector('[data-ff-sticky-trigger]');

			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (!entry.isIntersecting && entry.boundingClientRect.top < 0) {
							stickyBar.classList.add('ff-sticky-atc--visible');
						} else {
							stickyBar.classList.remove('ff-sticky-atc--visible');
						}
					});
				},
				{
					threshold: 0,
					rootMargin: '0px 0px -50px 0px',
				}
			);

			observer.observe(mainButton);

			if (stickyTrigger) {
				stickyTrigger.addEventListener('click', function (e) {
					e.preventDefault();

					if (mainButton.classList.contains('disabled')) {
						mainForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
						var firstSelect = mainForm.querySelector('select');
						if (firstSelect) {
							firstSelect.focus();
						}
					} else {
						mainButton.click();
					}
				});
			}
		}

		// 3. Sync variable product price with main price and tier buttons
		if (window.jQuery) {
			var $mainPrice = window.jQuery('.single-product div.product p.price');
			var originalPriceHtml = $mainPrice.length ? $mainPrice.html() : '';

			window.jQuery(document).on('found_variation', 'form.variations_form', function (event, variation) {
				if (variation && variation.display_price) {
					var basePrice = parseFloat(variation.display_price);

					// Update main 56px Fox Orange price
					if ($mainPrice.length && variation.price_html) {
						$mainPrice.html(variation.price_html);
					}

					if (stickyBar) {
						var stickyPrice = stickyBar.querySelector('.ff-sticky-atc__price');
						if (stickyPrice && variation.price_html) {
							stickyPrice.innerHTML = variation.price_html;
						}
					}

					// Update tier prices if container exists
					if (tierContainer) {
						var tier1 = tierContainer.querySelector('[data-tier-price="1"]');
						var tier3 = tierContainer.querySelector('[data-tier-price="3"]');
						var tier5 = tierContainer.querySelector('[data-tier-price="5"]');

						var btn3 = tierContainer.querySelector('.ff-tier-btn[data-qty="3"]');
						var btn5 = tierContainer.querySelector('.ff-tier-btn[data-qty="5"]');

						var disc3 = btn3 ? parseFloat(btn3.getAttribute('data-discount')) || 0 : 0;
						var disc5 = btn5 ? parseFloat(btn5.getAttribute('data-discount')) || 0 : 0;

						var p1 = basePrice;
						var p3 = basePrice * 3 * (1 - (disc3 / 100));
						var p5 = basePrice * 5 * (1 - (disc5 / 100));

						if (tier1) tier1.innerHTML = '$' + p1.toFixed(2);
						if (tier3) tier3.innerHTML = '$' + p3.toFixed(2);
						if (tier5) tier5.innerHTML = '$' + p5.toFixed(2);
					}
				}
			});

			window.jQuery(document).on('reset_data', 'form.variations_form', function () {
				if ($mainPrice.length && originalPriceHtml) {
					$mainPrice.html(originalPriceHtml);
				}
			});
		}
	});
})();
