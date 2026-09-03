/**
 * Shop AJAX Live Search — Foxfire Child Theme.
 *
 * Debounced search that filters the product grid in-place without a page reload.
 * Caches the default (unfiltered) grid to avoid server round-trips on clear.
 */
(function () {
	'use strict';

	/* ------------------------------------------------------------------ */
	/*  DOM refs                                                          */
	/* ------------------------------------------------------------------ */
	var form     = document.querySelector('.ff-shop-search-form');
	var input    = document.getElementById('ff-shop-search-input');
	var grid     = document.querySelector('[data-ff-product-grid]');
	var countEl  = document.querySelector('.ff-shop-toolbar__count .woocommerce-result-count');

	if (!form || !input || !grid) return;

	/* ------------------------------------------------------------------ */
	/*  State                                                             */
	/* ------------------------------------------------------------------ */
	var debounceTimer  = null;
	var activeRequest  = null;
	var lastQuery      = null;          // Track to avoid duplicate fetches
	var DEBOUNCE_MS    = 500;           // Wait 500ms after user stops typing

	// Cache the default grid so clearing is instant (no server call)
	var defaultGridHTML  = grid.innerHTML;
	var defaultCountText = countEl ? countEl.textContent : '';

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                           */
	/* ------------------------------------------------------------------ */

	/** Show or hide the ✕ clear button. */
	function toggleClear() {
		var clearBtn = form.querySelector('.ff-shop-search-clear');

		if (input.value.trim().length > 0) {
			if (!clearBtn) {
				clearBtn = document.createElement('a');
				clearBtn.href = '#';
				clearBtn.className = 'ff-shop-search-clear';
				clearBtn.setAttribute('aria-label', 'Clear search');
				clearBtn.textContent = '✕';
				clearBtn.addEventListener('click', onClear);
				input.parentElement.insertBefore(clearBtn, form.querySelector('.ff-shop-search-submit'));
			}
		} else if (clearBtn) {
			clearBtn.remove();
		}
	}

	/** Update URL query string without navigation. */
	function updateURL(query) {
		var url = new URL(window.location);
		if (query) {
			url.searchParams.set('s', query);
		} else {
			url.searchParams.delete('s');
		}
		window.history.replaceState(null, '', url);
	}

	/** Restore the default (unfiltered) grid instantly from cache. */
	function restoreDefault() {
		if (activeRequest) {
			activeRequest.abort();
			activeRequest = null;
		}
		clearTimeout(debounceTimer);
		grid.classList.remove('ff-shop-grid--loading');
		grid.innerHTML = defaultGridHTML;
		if (countEl) countEl.textContent = defaultCountText;
		updateURL('');
		lastQuery = '';
	}

	/* ------------------------------------------------------------------ */
	/*  AJAX fetch                                                        */
	/* ------------------------------------------------------------------ */
	function fetchProducts(query) {
		// Skip if same as last successful query
		if (query === lastQuery) return;

		// Empty query → restore cached default instantly
		if (!query) {
			restoreDefault();
			return;
		}

		// Abort any in-flight request
		if (activeRequest) {
			activeRequest.abort();
		}
		activeRequest = new AbortController();

		// Loading state
		grid.classList.add('ff-shop-grid--loading');

		var body = new FormData();
		body.append('action', 'foxfire_search_products');
		body.append('nonce', ffShopParams.nonce);
		body.append('search', query);

		fetch(ffShopParams.ajax_url, {
			method: 'POST',
			body: body,
			signal: activeRequest.signal,
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json.success) return;

				var data = json.data;
				lastQuery = query;

				if (data.count > 0) {
					grid.innerHTML = data.html;
				} else {
					grid.innerHTML =
						'<li class="ff-shop-empty-search">' +
						'<div class="ff-shop-empty-search__inner">' +
						'<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
						'<circle cx="11" cy="11" r="8"></circle>' +
						'<line x1="21" y1="21" x2="16.65" y2="16.65"></line>' +
						'<line x1="8" y1="8" x2="14" y2="14"></line>' +
						'<line x1="14" y1="8" x2="8" y2="14"></line>' +
						'</svg>' +
						'<p class="ff-shop-empty-search__title">No compounds found</p>' +
						'<p class="ff-shop-empty-search__hint">Try a different search term or <a href="#" class="ff-shop-empty-search__reset">view all compounds</a>.</p>' +
						'</div>' +
						'</li>';

					var resetLink = grid.querySelector('.ff-shop-empty-search__reset');
					if (resetLink) {
						resetLink.addEventListener('click', function (e) {
							e.preventDefault();
							input.value = '';
							toggleClear();
							restoreDefault();
						});
					}
				}

				if (countEl) {
					countEl.textContent = data.count_text;
				}

				updateURL(query);
			})
			.catch(function (err) {
				if (err.name === 'AbortError') return;
				console.error('[Foxfire Shop Search]', err);
			})
			.finally(function () {
				grid.classList.remove('ff-shop-grid--loading');
				activeRequest = null;
			});
	}

	/* ------------------------------------------------------------------ */
	/*  Event handlers                                                    */
	/* ------------------------------------------------------------------ */

	/** Debounced input — waits 500ms after user stops typing. */
	function onInput() {
		toggleClear();
		clearTimeout(debounceTimer);

		var query = input.value.trim();

		// If input is empty, restore default immediately (no wait)
		if (!query) {
			restoreDefault();
			return;
		}

		// Otherwise wait for user to stop typing
		debounceTimer = setTimeout(function () {
			fetchProducts(query);
		}, DEBOUNCE_MS);
	}

	/** Prevent default form submission — fire AJAX immediately. */
	function onSubmit(e) {
		e.preventDefault();
		clearTimeout(debounceTimer);
		fetchProducts(input.value.trim());
	}

	/** Clear button click — instant restore. */
	function onClear(e) {
		e.preventDefault();
		input.value = '';
		toggleClear();
		restoreDefault();
		input.focus();
	}

	/* ------------------------------------------------------------------ */
	/*  Bind events                                                       */
	/* ------------------------------------------------------------------ */
	input.addEventListener('input', onInput);
	form.addEventListener('submit', onSubmit);

	// Also handle the native search input "x" clear in some browsers
	input.addEventListener('search', function () {
		if (!input.value.trim()) {
			toggleClear();
			restoreDefault();
		}
	});

	// Bind any existing clear button rendered by PHP
	var existingClear = form.querySelector('.ff-shop-search-clear');
	if (existingClear) {
		existingClear.addEventListener('click', onClear);
	}
})();
