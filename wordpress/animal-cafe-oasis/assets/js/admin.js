/* どうぶつ編集画面の写真選び（WordPress のメディアライブラリを開きます） */
(function ($) {
	'use strict';

	function ids(box) {
		var v = box.find('.oasis-media__value').val();
		return v ? v.split(',').filter(Boolean) : [];
	}

	function write(box, list) {
		box.find('.oasis-media__value').val(list.join(','));
	}

	function thumb(id, url) {
		return '<span class="oasis-media__item" data-id="' + id + '">' +
			'<img src="' + url + '" alt="">' +
			'<button type="button" class="oasis-media__remove" aria-label="この写真を外す">&times;</button>' +
			'</span>';
	}

	$(document).on('click', '.oasis-media__pick', function (e) {
		e.preventDefault();
		var box      = $(this).closest('.oasis-media');
		var multiple = box.data('multiple') === 1 || box.data('multiple') === '1';

		var frame = wp.media({
			title: multiple ? '写真を選ぶ（複数選べます）' : '画像を選ぶ',
			button: { text: 'この写真を使う' },
			library: { type: 'image' },
			multiple: multiple
		});

		frame.on('select', function () {
			var sel  = frame.state().get('selection');
			var list = multiple ? ids(box) : [];

			sel.each(function (att) {
				var a  = att.toJSON();
				var id = String(a.id);
				if (list.indexOf(id) !== -1) { return; }
				list.push(id);
				var url = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
				box.find('.oasis-media__list').append(thumb(id, url));
			});

			if (!multiple && list.length > 1) { list = [list[list.length - 1]]; }
			if (!multiple) {
				box.find('.oasis-media__item').not(':last').remove();
			}
			write(box, list);
		});

		frame.open();
	});

	$(document).on('click', '.oasis-media__remove', function (e) {
		e.preventDefault();
		var item = $(this).closest('.oasis-media__item');
		var box  = item.closest('.oasis-media');
		var id   = String(item.data('id'));
		write(box, ids(box).filter(function (x) { return x !== id; }));
		item.remove();
	});

	$(document).on('click', '.oasis-media__clear', function (e) {
		e.preventDefault();
		var box = $(this).closest('.oasis-media');
		box.find('.oasis-media__list').empty();
		write(box, []);
	});

	// ドラッグで並べ替え（サブ写真の順番＝サムネイルの並び順）
	$(function () {
		if (!$.fn.sortable) { return; }
		$('.oasis-media[data-multiple="1"] .oasis-media__list').sortable({
			items: '.oasis-media__item',
			cursor: 'move',
			update: function () {
				var box  = $(this).closest('.oasis-media');
				var list = [];
				$(this).find('.oasis-media__item').each(function () { list.push(String($(this).data('id'))); });
				write(box, list);
			}
		});
	});
})(jQuery);
