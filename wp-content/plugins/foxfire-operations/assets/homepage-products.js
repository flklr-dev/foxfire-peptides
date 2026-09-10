( function ( $ ) {
	'use strict';

	function initializeHomepageProducts() {
		const list = document.getElementById( 'foxfire-homepage-product-rows' );
		const search = document.getElementById( 'foxfire-homepage-product-search' );
		if ( ! list ) {
			return;
		}

		function rows() {
			return Array.from( list.querySelectorAll( '[data-foxfire-homepage-product-row]' ) );
		}

		function validateUniqueSelections() {
			const selected = new Set();
			let firstDuplicate = null;

			rows().forEach( function ( row ) {
				const select = row.querySelector( '.ff-homepage-products__select' );
				if ( ! select ) {
					return;
				}

				select.setCustomValidity( '' );
				if ( select.value && selected.has( select.value ) ) {
					select.setCustomValidity( 'Each homepage product may be selected only once.' );
					firstDuplicate = firstDuplicate || select;
				}
				selected.add( select.value );
			} );

			return firstDuplicate;
		}

		function synchronizeRows() {
			const currentRows = rows();
			currentRows.forEach( function ( row, index ) {
				const position = row.querySelector( '.ff-homepage-products__position' );
				const label = row.querySelector( 'label[for]' );
				const select = row.querySelector( '.ff-homepage-products__select' );
				const drag = row.querySelector( '.ff-homepage-products__drag' );
				const up = row.querySelector( '.ff-homepage-products__up' );
				const down = row.querySelector( '.ff-homepage-products__down' );

				if ( position ) {
					position.textContent = String( index + 1 );
				}
				if ( label && select ) {
					const selectId = 'foxfire-homepage-product-' + index;
					select.id = selectId;
					label.htmlFor = selectId;
					label.textContent = 'Homepage product at position ' + ( index + 1 );
				}
				if ( drag ) {
					drag.setAttribute( 'aria-label', 'Drag homepage position ' + ( index + 1 ) + ' to reorder' );
				}
				if ( up ) {
					up.disabled = 0 === index;
					up.setAttribute( 'aria-label', 'Move homepage position ' + ( index + 1 ) + ' up' );
				}
				if ( down ) {
					down.disabled = index === currentRows.length - 1;
					down.setAttribute( 'aria-label', 'Move homepage position ' + ( index + 1 ) + ' down' );
				}
			} );

			validateUniqueSelections();
		}

		list.addEventListener( 'click', function ( event ) {
			const button = event.target.closest( 'button' );
			const row = event.target.closest( '[data-foxfire-homepage-product-row]' );
			if ( ! button || ! row ) {
				return;
			}

			if ( button.classList.contains( 'ff-homepage-products__up' ) && row.previousElementSibling ) {
				list.insertBefore( row, row.previousElementSibling );
			} else if ( button.classList.contains( 'ff-homepage-products__down' ) && row.nextElementSibling ) {
				list.insertBefore( row.nextElementSibling, row );
			} else {
				return;
			}

			synchronizeRows();
			row.querySelector( '.ff-homepage-products__select' )?.focus();
		} );

		list.addEventListener( 'change', validateUniqueSelections );

		if ( search ) {
			search.addEventListener( 'input', function () {
				const query = search.value.trim().toLocaleLowerCase();
				rows().forEach( function ( row ) {
					const select = row.querySelector( '.ff-homepage-products__select' );
					if ( ! select ) {
						return;
					}

					Array.from( select.options ).forEach( function ( option ) {
						option.hidden = Boolean( query ) && Boolean( option.value ) && ! option.selected && ! option.text.toLocaleLowerCase().includes( query );
					} );
				} );
			} );
		}

		const form = list.closest( 'form' );
		if ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				const duplicate = validateUniqueSelections();
				if ( duplicate ) {
					event.preventDefault();
					duplicate.reportValidity();
				}
			} );
		}

		if ( $ && $.fn && $.fn.sortable ) {
			$( list ).sortable( {
				axis: 'y',
				handle: '.ff-homepage-products__drag',
				items: '> tr',
				placeholder: 'ff-homepage-products__placeholder',
				update: synchronizeRows
			} );
		}

		synchronizeRows();
	}

	document.addEventListener( 'DOMContentLoaded', initializeHomepageProducts );
}( window.jQuery ) );
