/**
 * Foxfire Peptides — Testing / COA Page JavaScript (Chunk 1I)
 *
 * Provides real-time instant search filtering on the batch-to-COA directory table.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var searchInput = document.querySelector('[data-ff-coa-filter]');
		var tableRows   = document.querySelectorAll('[data-ff-coa-row]');
		var emptyMsg    = document.getElementById('ff-coa-empty-msg');

		if (!searchInput || !tableRows.length) {
			return;
		}

		searchInput.addEventListener('input', function (e) {
			var query = e.target.value.toLowerCase().trim();
			var visibleCount = 0;

			tableRows.forEach(function (row) {
				var text = row.getAttribute('data-compound') || '';
				if (!query || text.indexOf(query) !== -1) {
					row.style.display = '';
					visibleCount++;
				} else {
					row.style.display = 'none';
				}
			});

			if (emptyMsg) {
				emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
			}
		});
	});
})();
