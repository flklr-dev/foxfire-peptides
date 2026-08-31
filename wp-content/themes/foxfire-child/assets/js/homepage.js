/**
 * Foxfire Peptides — Homepage Scripts (Scroll Animations & Micro-Interactions)
 *
 * Utilizes native IntersectionObserver for smooth, GPU-accelerated
 * scroll-triggered entrance animations across homepage sections.
 *
 * @package Foxfire_Child
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var revealElements = document.querySelectorAll('.ff-reveal');

		if (!revealElements.length) {
			return;
		}

		if (prefersReducedMotion || !('IntersectionObserver' in window)) {
			// Instantly reveal all elements if user prefers reduced motion or if IntersectionObserver is unsupported
			revealElements.forEach(function (el) {
				el.classList.add('ff-reveal--visible');
			});
			return;
		}

		var observerOptions = {
			root: null,
			rootMargin: '0px 0px -50px 0px',
			threshold: 0.1,
		};

		var revealObserver = new IntersectionObserver(function (entries, observer) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('ff-reveal--visible');
					observer.unobserve(entry.target);
				}
			});
		}, observerOptions);

		revealElements.forEach(function (el) {
			revealObserver.observe(el);
		});
	});
})();
