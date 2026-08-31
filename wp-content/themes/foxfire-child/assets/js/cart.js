/**
 * Foxfire Peptides — Full Cart Page Interactivity (Chunk 1J)
 *
 * @package Foxfire_Child
 */

(function ($) {
	'use strict';

	var updateTimer = null;

	function initCartSteppers() {
		// Quantity Stepper Plus/Minus Click
		$(document).off('click', '.ff-cart-card .ff-qty-btn').on('click', '.ff-cart-card .ff-qty-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $wrap = $btn.closest('.ff-qty-wrapper');
			var $input = $wrap.find('input.qty');
			if (!$input.length) return;

			var action = $btn.data('action');
			var currentVal = parseFloat($input.val()) || 0;
			var min = parseFloat($input.attr('min')) || 0;
			var max = parseFloat($input.attr('max')) || Infinity;
			var step = parseFloat($input.attr('step')) || 1;

			var newVal = currentVal;
			if (action === 'plus') {
				if (currentVal < max) {
					newVal = currentVal + step;
				}
			} else if (action === 'minus') {
				if (currentVal > min) {
					newVal = Math.max(min, currentVal - step);
				}
			}

			if (newVal !== currentVal) {
				$input.val(newVal).trigger('change');
				triggerAutoUpdate();
			}
		});

		// Listen to manual input change
		$(document).off('change', '.ff-cart-card input.qty').on('change', '.ff-cart-card input.qty', function () {
			triggerAutoUpdate();
		});
	}

	function triggerAutoUpdate() {
		var $updateBtn = $('button[name="update_cart"]');
		if ($updateBtn.length) {
			$updateBtn.prop('disabled', false).attr('aria-disabled', 'false');

			clearTimeout(updateTimer);
			$('.ff-cart-items-list').addClass('is-updating');

			updateTimer = setTimeout(function () {
				$updateBtn.trigger('click');
			}, 500);
		}
	}

	$(document).ready(function () {
		initCartSteppers();
	});

	// Re-init after WooCommerce AJAX cart reload
	$(document.body).on('updated_wc_div', function () {
		$('.ff-cart-items-list').removeClass('is-updating');
		initCartSteppers();
	});

})(jQuery);
