/**
 * Foxfire Peptides — Product Detail Page (PDP) Interactions (Chunk 1G).
 *
 * Handles:
 * - Administrator-configured quantity tier pill selection and dynamic price updates.
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

		// Quantity presets are supplied by the operations plugin, never fixed here.
		var tierButtons = tierContainer ? Array.from(tierContainer.querySelectorAll('.ff-tier-btn')) : [];
		var initialTierHtml = tierContainer ? tierContainer.innerHTML : '';
		var quantityUnavailable = false;
		var presetMax = tierContainer ? Number(tierContainer.dataset.maxQuantity) : -1;
		function selectTier(btn) {
			tierButtons.forEach(function (other) {
				var active = other === btn;
				other.classList.toggle('is-active', active);
				other.setAttribute('aria-pressed', active ? 'true' : 'false');
			});
			if (btn && qtyInput) {
				qtyInput.value = btn.dataset.qty;
				qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
			}
			var label = btn && btn.querySelector('.ff-tier-btn__pricing');
			var summary = tierContainer && tierContainer.querySelector('.ff-tier-selected-price');
			if (summary) summary.innerHTML = label ? label.innerHTML : '';
			var stickyPrice = stickyBar && stickyBar.querySelector('.ff-sticky-atc__price');
			if (stickyPrice && label) {
				stickyPrice.innerHTML = label.innerHTML;
				var totalLabel = document.createElement('small');
				totalLabel.textContent = ' ' + stickyPrice.dataset.totalLabel;
				stickyPrice.appendChild(totalLabel);
			}
		}
		function syncTierAvailability() {
			var min = qtyInput ? Number(qtyInput.min || 1) : 1;
			var max = qtyInput && qtyInput.max !== '' ? Number(qtyInput.max) : Infinity;
			if (presetMax >= 0) max = Math.min(max, presetMax);
			tierButtons.forEach(function (btn) {
				var qty = Number(btn.dataset.qty);
				btn.disabled = qty < min || qty > max;
			});
			var current = tierButtons.find(function (btn) { return !btn.disabled && btn.classList.contains('is-active'); });
			var next = current || tierButtons.find(function (btn) { return !btn.disabled; });
			selectTier(next);
			if (mainButton) {
				if (!next) mainButton.disabled = true;
				else if (quantityUnavailable && !mainButton.classList.contains('disabled')) mainButton.disabled = false;
			}
			quantityUnavailable = !next;
			var note = tierContainer && tierContainer.querySelector('.ff-quantity-stock-note');
			if (note) note.hidden = !!next;
		}
		function bindTierButtons() {
			tierButtons.forEach(function (btn) {
				btn.addEventListener('click', function (event) {
					event.preventDefault();
					if (!btn.disabled) selectTier(btn);
				});
			});
		}
		bindTierButtons();
		if (tierContainer && mainForm) syncTierAvailability();
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
				if (variation && typeof variation.display_price === 'number') {

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

					// Use tax-aware prices computed on the server for this strength.
					if (tierContainer && variation.foxfire_quantity_prices) {
						presetMax = variation.max_qty === '' ? -1 : Number(variation.max_qty);
						tierButtons.forEach(function (btn) {
							var price = variation.foxfire_quantity_prices[btn.dataset.qty];
							var label = btn.querySelector('.ff-tier-btn__pricing');
							if (price && label) label.innerHTML = price.html;
							var badge = btn.querySelector('.ff-tier-btn__badge');
							if (badge) badge.remove();
							if (price && price.discount > 0) {
								badge = document.createElement('span');
								badge.className = 'ff-tier-btn__badge';
								badge.textContent = 'Save ' + price.discount + '%';
								btn.querySelector('.ff-tier-btn__header').appendChild(badge);
							}
						});
						syncTierAvailability();
					}
				}
			});

			window.jQuery(document).on('reset_data', 'form.variations_form', function () {
				if (tierContainer) {
					presetMax = Number(tierContainer.dataset.maxQuantity);
					tierContainer.innerHTML = initialTierHtml;
					tierButtons = Array.from(tierContainer.querySelectorAll('.ff-tier-btn'));
					bindTierButtons();
					syncTierAvailability();
				}
				if ($mainPrice.length && originalPriceHtml) {
					$mainPrice.html(originalPriceHtml);
				}
			});
		}
	});
})();
