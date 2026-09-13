(function () {
	'use strict';
	function updateButtons(editor) {
		var count = editor.querySelector('tbody').rows.length;
		var add = editor.querySelector('.ff-quantity-add');
		if (add) add.disabled = count >= 12;
		editor.querySelectorAll('.ff-quantity-remove').forEach(function (button) { button.disabled = count <= 1; });
	}
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.ff-quantity-editor').forEach(updateButtons);
	});
	document.addEventListener('click', function (event) {
		var add = event.target.closest('.ff-quantity-add');
		var remove = event.target.closest('.ff-quantity-remove');
		if (add) {
			var editor = add.closest('.ff-quantity-editor');
			var body = editor.querySelector('tbody');
			if (body.rows.length >= 12) return;
			var index = Number(editor.dataset.nextIndex || body.rows.length);
			editor.dataset.nextIndex = index + 1;
			body.insertAdjacentHTML('beforeend', editor.querySelector('template').innerHTML.replace(/__INDEX__/g, String(index)));
			body.lastElementChild.querySelector('input').focus();
			updateButtons(editor);
		} else if (remove) {
			var editor = remove.closest('.ff-quantity-editor');
			if (!editor.dataset.nextIndex) editor.dataset.nextIndex = editor.querySelector('tbody').rows.length;
			if (editor.querySelector('tbody').rows.length > 1) remove.closest('tr').remove();
			updateButtons(editor);
		}
	});
	document.addEventListener('change', function (event) {
		if (!event.target.matches('.ff-quantity-editor select')) return;
		var value = event.target.closest('tr').querySelector('input[name$="[value]"]');
		value.max = event.target.value === 'discount' ? '100' : '1000000';
		value.disabled = event.target.value === 'inherit';
	});
})();
