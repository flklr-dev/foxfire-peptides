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
