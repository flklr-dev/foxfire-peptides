/**
 * Foxfire 21+ gate state and keyboard behavior.
 *
 * The server owns cookie creation. This early head script reads that cookie
 * before body markup is parsed, preventing a gate flash on verified visits.
 */
(function () {
	'use strict';

	var cookieName = 'foxfire_age_verified';
	var verified = document.cookie
		.split(';')
		.map(function (part) {
			return part.trim();
		})
		.some(function (part) {
			return part === cookieName + '=21';
		});

	document.documentElement.classList.add(verified ? 'ff-age-verified' : 'ff-age-unverified');

	function initializeGate() {
		var gate = document.getElementById('ff-age-gate');

		if (!gate || verified) {
			if (gate) {
				gate.hidden = true;
				gate.setAttribute('aria-hidden', 'true');
			}
			return;
		}

		var form = gate.querySelector('.ff-age-gate__form');
		var enterButton = gate.querySelector('.ff-age-gate__enter');
		var focusable = Array.prototype.slice.call(
			gate.querySelectorAll('button:not([disabled]), a[href]')
		);
		var background = Array.prototype.slice.call(document.body.children).filter(function (element) {
			return element !== gate && element.tagName !== 'SCRIPT';
		});

		gate.hidden = false;
		gate.setAttribute('aria-hidden', 'false');
		background.forEach(function (element) {
			element.inert = true;
		});

		var backgroundObserver = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				Array.prototype.forEach.call(mutation.addedNodes, function (node) {
					if (node.nodeType === 1 && node !== gate && node.tagName !== 'SCRIPT') {
						node.inert = true;
					}
				});
			});
		});
		backgroundObserver.observe(document.body, { childList: true });

		window.setTimeout(function () {
			if (enterButton) {
				enterButton.focus();
			}
		}, 0);

		gate.addEventListener('keydown', function (event) {
			if (event.key !== 'Tab' || focusable.length === 0) {
				return;
			}

			var first = focusable[0];
			var last = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		if (form && enterButton) {
			form.addEventListener('submit', function () {
				enterButton.disabled = true;
				enterButton.textContent = 'ENTERING…';
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeGate);
	} else {
		initializeGate();
	}
})();
