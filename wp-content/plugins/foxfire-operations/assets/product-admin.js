( function () {
	'use strict';

	const config = window.FoxfireProductAdmin || {};

	function fieldValue( name, fallback ) {
		const wrapper = document.querySelector( '.acf-field[data-name="' + name + '"]' );
		if ( ! wrapper ) {
			return fallback;
		}

		const checked = wrapper.querySelector( 'input[type="checkbox"]' );
		if ( checked ) {
			return checked.checked ? 1 : 0;
		}

		const input = wrapper.querySelector( 'input, select' );
		return input ? input.value : fallback;
	}

	function money( amount ) {
		try {
			return new Intl.NumberFormat( undefined, {
				style: 'currency',
				currency: config.currency || 'USD',
				minimumFractionDigits: Number( config.decimals || 2 ),
				maximumFractionDigits: Number( config.decimals || 2 )
			} ).format( amount );
		} catch ( error ) {
			return ( config.currencySymbol || '$' ) + Number( amount ).toFixed( Number( config.decimals || 2 ) );
		}
	}

	function activeSimplePrice( current ) {
		const preview = document.querySelector( '.foxfire-pricing-preview[data-product-type="simple"]' );
		if ( ! preview ) {
			return current;
		}

		const regular = document.querySelector( '#_regular_price' );
		const sale = document.querySelector( '#_sale_price' );
		const saleFrom = document.querySelector( '#_sale_price_dates_from' );
		const saleTo = document.querySelector( '#_sale_price_dates_to' );
		const now = new Date();
		const startsAt = saleFrom && saleFrom.value ? new Date( saleFrom.value + 'T00:00:00' ) : null;
		const endsAt = saleTo && saleTo.value ? new Date( saleTo.value + 'T23:59:59' ) : null;
		const saleIsActive = ( ! startsAt || startsAt <= now ) && ( ! endsAt || endsAt >= now );

		if ( sale && sale.value !== '' && Number.isFinite( Number( sale.value ) ) && saleIsActive ) {
			return Number( sale.value );
		}
		if ( regular && regular.value !== '' && Number.isFinite( Number( regular.value ) ) ) {
			return Number( regular.value );
		}

		return current;
	}

	function renderPreview() {
		const enabled = Number( fieldValue( 'foxfire_tier_enable', 1 ) ) === 1;
		const tier3 = Math.min( 50, Math.max( 0, Number( fieldValue( 'foxfire_tier_3_discount', 0 ) ) || 0 ) );
		const tier5 = Math.min( 50, Math.max( 0, Number( fieldValue( 'foxfire_tier_5_discount', 0 ) ) || 0 ) );

		document.querySelectorAll( '.foxfire-pricing-preview tbody tr' ).forEach( function ( row ) {
			let base = Number( row.dataset.basePrice || 0 );
			base = activeSimplePrice( base );
			const prices = {
				1: base,
				3: ( enabled ? base * ( 1 - tier3 / 100 ) : base ) * 3,
				5: ( enabled ? base * ( 1 - tier5 / 100 ) : base ) * 5
			};

			Object.keys( prices ).forEach( function ( tier ) {
				const cell = row.querySelector( '[data-tier="' + tier + '"]' );
				if ( cell ) {
					cell.textContent = money( prices[ tier ] );
				}
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		renderPreview();
		document.addEventListener( 'input', renderPreview );
		document.addEventListener( 'change', renderPreview );
	} );
}() );
