/**
 * Foxfire Peptides — Product Detail Page (PDP) Interactions (Chunk 1G).
 *
 * Handles:
 * - Mobile sticky Add to Cart bar visibility when scrolling past the main buy button.
 * - Smooth scroll & focus to variation options if an option isn't selected.
 * - Dynamic price sync with WooCommerce variable product dropdowns.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var stickyBar = document.getElementById('ff-sticky-atc');
		if (!stickyBar) {
			return;
		}

		var mainForm = document.querySelector('form.cart');
		var mainButton = document.querySelector('form.cart .single_add_to_cart_button');
		var stickyTrigger = stickyBar.querySelector('[data-ff-sticky-trigger]');

		if (!mainForm || !mainButton) {
			return;
		}

		// Show/hide sticky bar based on scroll position past the main button
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

		// Sticky button click handler
		if (stickyTrigger) {
			stickyTrigger.addEventListener('click', function (e) {
				e.preventDefault();

				// If variable product and button is disabled (no selection made), scroll to variation form
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

		// Listen to WooCommerce variations found event via jQuery if available
		if (window.jQuery) {
			window.jQuery(document).on('found_variation', 'form.variations_form', function (event, variation) {
				if (variation && variation.price_html) {
					var stickyPrice = stickyBar.querySelector('.ff-sticky-atc__price');
					if (stickyPrice) {
						stickyPrice.innerHTML = variation.price_html;
					}
				}
			});
		}
	});
})();
