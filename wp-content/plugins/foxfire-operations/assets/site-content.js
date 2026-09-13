( function () {
	'use strict';

	function initialiseImageField( field ) {
		const input = field.querySelector( 'input[type="hidden"]' );
		const preview = field.querySelector( '.ff-content-image-field__preview' );
		const image = preview ? preview.querySelector( 'img' ) : null;
		const choose = field.querySelector( '.ff-content-image-field__choose' );
		const remove = field.querySelector( '.ff-content-image-field__remove' );

		if ( ! input || ! preview || ! image || ! choose || ! remove || ! window.wp || ! wp.media ) {
			return;
		}

		choose.addEventListener( 'click', function () {
			const frame = wp.media( {
				title: field.dataset.dialogTitle || 'Choose image',
				button: { text: field.dataset.chooseLabel || 'Use this image' },
				library: { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				const attachment = frame.state().get( 'selection' ).first().toJSON();
				const previewUrl = attachment.sizes && attachment.sizes.medium
					? attachment.sizes.medium.url
					: attachment.url;

				input.value = String( attachment.id );
				image.src = previewUrl;
				preview.hidden = false;
				remove.hidden = false;
				choose.textContent = field.dataset.replaceLabel || 'Replace image';
			} );

			frame.open();
		} );

		remove.addEventListener( 'click', function () {
			input.value = '';
			image.removeAttribute( 'src' );
			preview.hidden = true;
			remove.hidden = true;
			choose.textContent = field.dataset.emptyLabel || 'Choose image';
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.ff-content-image-field' ).forEach( initialiseImageField );
	} );
}() );
