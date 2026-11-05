/*! Bireme Builder | Desenvolvido por @toni - 2WP | https://2wp.com.br */
(function ($) {
	'use strict';

	var cfg = window.BiremeTopics || {};

	$(function () {
		var $box = $('.bireme-topics');
		if (!$box.length) {
			return;
		}

		var $search = $box.find('.bireme-topics__search');
		var $list = $box.find('.bireme-topics__list');
		var postId = String($box.data('post-id') || '');

		function selectedIds() {
			return $list.find('.bireme-topics__item').map(function () {
				return String($(this).data('id'));
			}).get();
		}

		function addItem(id, label) {
			if ($.inArray(String(id), selectedIds()) !== -1) {
				return;
			}
			if (selectedIds().length >= cfg.max) {
				window.alert(cfg.i18n.limit);
				return;
			}
			var $item = $('<li class="bireme-topics__item"/>').attr('data-id', id);
			$item.append('<span class="dashicons dashicons-menu bireme-topics__handle" aria-hidden="true"></span>');
			$item.append($('<span class="bireme-topics__title"/>').text(label));
			$item.append($('<button type="button" class="bireme-topics__remove">&times;</button>').attr('aria-label', cfg.i18n.remove));
			$item.append($('<input type="hidden" name="bireme_featured_topics[]"/>').val(id));
			$list.append($item);
		}

		$list.sortable({
			handle: '.bireme-topics__handle',
			axis: 'y',
			placeholder: 'bireme-topics__placeholder'
		});

		$list.on('click', '.bireme-topics__remove', function () {
			$(this).closest('.bireme-topics__item').remove();
		});

		$search.autocomplete({
			minLength: 2,
			delay: 250,
			source: function (request, response) {
				$.getJSON(cfg.ajaxUrl, {
					action: cfg.action,
					nonce: cfg.nonce,
					term: request.term,
					exclude: selectedIds().concat(postId).join(',')
				}).done(function (res) {
					var items = res && res.success ? res.data : [];
					if (!items.length) {
						response([{ id: 0, label: cfg.i18n.noMatch, value: '' }]);
						return;
					}
					response($.map(items, function (item) {
						return { id: item.id, label: item.label + ' — ' + item.date, title: item.label, value: '' };
					}));
				}).fail(function () {
					response([]);
				});
			},
			select: function (event, ui) {
				event.preventDefault();
				if (ui.item.id) {
					addItem(ui.item.id, ui.item.title);
				}
				$search.val('');
			},
			focus: function (event) {
				event.preventDefault();
			}
		});
	});
})(jQuery);
