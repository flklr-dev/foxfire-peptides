/**
 * Foxfire shell — mobile navigation and submenu toggles (Chunk 1D).
 */
(function () {
	'use strict';

	var body = document.body;
	var navToggle = document.querySelector('[data-ff-nav-toggle]');
	var navPanel = document.querySelector('[data-ff-nav-panel]');
	var submenuToggles = document.querySelectorAll('[data-ff-submenu-toggle]');
	var backdrop = null;
	var desktopQuery = window.matchMedia('(min-width: 1024px)');

	function isDesktop() {
		return desktopQuery.matches;
	}

	function ensureBackdrop() {
		if (backdrop) {
			return backdrop;
		}

		backdrop = document.createElement('div');
		backdrop.className = 'ff-nav-backdrop';
		backdrop.setAttribute('data-ff-nav-backdrop', '');
		backdrop.addEventListener('click', closeNav);
		body.appendChild(backdrop);

		return backdrop;
	}

	function setNavOpen(isOpen) {
		body.classList.toggle('ff-nav-is-open', isOpen);

		if (navToggle) {
			navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		}

		if (!isDesktop()) {
			ensureBackdrop();
			body.style.overflow = isOpen ? 'hidden' : '';
		} else {
			body.style.overflow = '';
		}
	}

	function closeNav() {
		setNavOpen(false);
	}

	function openNav() {
		if (!isDesktop()) {
			setNavOpen(true);
		}
	}

	function toggleNav() {
		if (isDesktop()) {
			return;
		}

		setNavOpen(!body.classList.contains('ff-nav-is-open'));
	}

	function toggleSubmenu(button) {
		var item = button.closest('.ff-primary-nav__item--has-dropdown');

		if (!item) {
			return;
		}

		var isOpen = item.classList.toggle('ff-primary-nav__item--submenu-open');
		button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	}

	function handleDesktopChange() {
		if (isDesktop()) {
			closeNav();
			submenuToggles.forEach(function (button) {
				button.setAttribute('aria-expanded', 'false');
				var item = button.closest('.ff-primary-nav__item--has-dropdown');

				if (item) {
					item.classList.remove('ff-primary-nav__item--submenu-open');
				}
			});
		}
	}

	if (navToggle) {
		navToggle.addEventListener('click', toggleNav);
	}

	submenuToggles.forEach(function (button) {
		button.addEventListener('click', function () {
			if (!isDesktop()) {
				toggleSubmenu(button);
			}
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && body.classList.contains('ff-nav-is-open')) {
			closeNav();
			if (navToggle) {
				navToggle.focus();
			}
		}
	});

	if (typeof desktopQuery.addEventListener === 'function') {
		desktopQuery.addEventListener('change', handleDesktopChange);
	} else if (typeof desktopQuery.addListener === 'function') {
		desktopQuery.addListener(handleDesktopChange);
	}

	handleDesktopChange();
})();
