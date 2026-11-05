/*! Bireme Builder | Desenvolvido por @toni - 2WP | https://2wp.com.br */
/* global jQuery, wp, tinymce */
(function ($) {
	'use strict';

	var cfg = window.BiremeBuilder || {};
	var sections = cfg.sections || {};
	var i18n = cfg.i18n || {};

	var editorSeq = 0;
	var serializeTimer = null;
	var $root, $canvas, $input;

	/* ------------------------------------------------------------------ *
	 * Utilitários
	 * ------------------------------------------------------------------ */

	function t(key) {
		return i18n[key] || key;
	}

	function clone(value) {
		return value === undefined ? undefined : JSON.parse(JSON.stringify(value));
	}

	function uid() {
		return 'bb' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
	}

	function defaultsFor(fields) {
		var data = {};
		$.each(fields || [], function (i, field) {
			data[field.key] = clone(field['default']);
		});
		return data;
	}

	function valueFor(data, field) {
		if (data && Object.prototype.hasOwnProperty.call(data, field.key)) {
			return data[field.key];
		}
		return clone(field['default']);
	}

	function iconButton(className, icon, label) {
		return $('<button type="button" class="bireme-icon-btn"></button>')
			.addClass(className)
			.attr({ title: label, 'aria-label': label })
			.append($('<span class="dashicons" aria-hidden="true"></span>').addClass(icon));
	}

	/* ------------------------------------------------------------------ *
	 * Editores WYSIWYG (TinyMCE não sobrevive a mover o iframe no DOM:
	 * remove antes de arrastar e recria depois)
	 * ------------------------------------------------------------------ */

	function hasEditorApi() {
		return !!(window.wp && wp.editor && typeof wp.editor.initialize === 'function');
	}

	function editorContent(id) {
		if (!id) {
			return '';
		}
		if (window.tinymce) {
			var ed = tinymce.get(id);
			if (ed && !ed.isHidden()) {
				ed.save();
			}
		}
		return $('#' + id).val() || '';
	}

	function initEditors($scope) {
		if (!hasEditorApi()) {
			return;
		}
		$scope.find('textarea.bireme-wysiwyg').each(function () {
			var $ta = $(this);
			if ($ta.data('biremeEditor') || !$ta.is(':visible')) {
				return;
			}
			wp.editor.initialize(this.id, {
				tinymce: {
					wpautop: true,
					height: 180,
					toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,undo,redo',
					toolbar2: '',
					setup: function (ed) {
						ed.on('change keyup', scheduleSerialize);
					}
				},
				quicktags: { buttons: 'strong,em,link,ul,ol,li,close' },
				mediaButtons: false
			});
			$ta.data('biremeEditor', true);
		});
	}

	function destroyEditors($scope) {
		if (!hasEditorApi()) {
			return;
		}
		$scope.find('textarea.bireme-wysiwyg').each(function () {
			var $ta = $(this);
			if (!$ta.data('biremeEditor')) {
				return;
			}
			$ta.val(editorContent(this.id));
			wp.editor.remove(this.id);
			$ta.css('display', '').data('biremeEditor', false);
		});
	}

	function initPlugins($scope) {
		$scope.find('input.bireme-color').each(function () {
			var $color = $(this);
			if ($color.closest('.wp-picker-container').length || !$.fn.wpColorPicker) {
				return;
			}
			$color.wpColorPicker({ change: scheduleSerialize, clear: scheduleSerialize });
		});

		$scope.find('.bireme-column__items').addBack('.bireme-column__items').each(function () {
			var $items = $(this);
			if (!$items.hasClass('ui-sortable')) {
				$items.sortable(cardSortableOptions('.bireme-canvas, .bireme-column__items'));
			}
		});

		$scope.find('.bireme-repeater__rows').addBack('.bireme-repeater__rows').each(function () {
			var $rows = $(this);
			if ($rows.hasClass('ui-sortable')) {
				return;
			}
			$rows.sortable({
				handle: '.bireme-row__handle',
				axis: 'y',
				placeholder: 'bireme-row-placeholder',
				forcePlaceholderSize: true,
				start: function (e, ui) {
					destroyEditors(ui.item);
				},
				stop: function (e, ui) {
					initEditors(ui.item);
					refreshRepeater($rows.closest('.bireme-repeater'));
					serialize();
				}
			});
		});
	}

	function activate($scope) {
		initPlugins($scope);
		$scope.find('.bireme-fields').addBack('.bireme-fields').each(function () {
			applyConditions($(this));
		});
		initEditors($scope);
		$scope.find('.bireme-repeater').addBack('.bireme-repeater').each(function () {
			refreshRepeater($(this));
		});
		$scope.find('.bireme-card').addBack('.bireme-card').each(function () {
			updateSummary($(this));
		});
	}

	/* ------------------------------------------------------------------ *
	 * Renderização dos campos
	 * ------------------------------------------------------------------ */

	function renderFields(fields, data) {
		var $wrap = $('<div class="bireme-fields"></div>').data('fields', fields || []);
		$.each(fields || [], function (i, field) {
			$wrap.append(renderField(field, valueFor(data, field)));
		});
		return $wrap;
	}

	/** Mostra/oculta campos com 'condition' conforme o valor dos campos irmãos. */
	function applyConditions($wrap) {
		var fields = $wrap.data('fields') || [];
		var byKey = {};
		$.each(fields, function (i, field) {
			byKey[field.key] = field;
		});

		$.each(fields, function (i, field) {
			if (!field.condition) {
				return;
			}
			var met = true;
			$.each(field.condition, function (key, expected) {
				var $sibling = $wrap.children('.bireme-field[data-key="' + key + '"]');
				if (!byKey[key] || !$sibling.length) {
					return;
				}
				var value = String(readField($sibling, byKey[key]));
				var allowed = $.isArray(expected) ? $.map(expected, String) : [String(expected)];
				if ($.inArray(value, allowed) === -1) {
					met = false;
					return false;
				}
			});

			var $field = $wrap.children('.bireme-field[data-key="' + field.key + '"]');
			var wasHidden = $field.hasClass('is-condition-hidden');
			$field.toggleClass('is-condition-hidden', !met);
			if (met && wasHidden) {
				initEditors($field);
			}
		});
	}

	function renderField(field, value) {
		var $field = $('<div class="bireme-field"></div>')
			.addClass('bireme-field--' + field.type)
			.attr({ 'data-key': field.key, 'data-type': field.type });
		var $control = $('<div class="bireme-field__control"></div>');

		if (field.type !== 'checkbox') {
			$field.append($('<label class="bireme-field__label"></label>').text(field.label));
		}

		switch (field.type) {
			case 'textarea':
				$control.append(
					$('<textarea class="widefat" rows="4"></textarea>').attr('placeholder', field.placeholder || '').val(value || '')
				);
				break;

			case 'wysiwyg':
				editorSeq++;
				$control.append(
					$('<textarea class="bireme-wysiwyg widefat" rows="8"></textarea>').attr('id', 'bireme_editor_' + editorSeq).val(value || '')
				);
				break;

			case 'image':
				$control.append(renderImage(value));
				break;

			case 'link':
				$control.append(renderLink(value));
				break;

			case 'color':
				$control.append($('<input type="text" class="bireme-color">').val(value || ''));
				break;

			case 'select':
				var $select = $('<select></select>');
				$.each(field.options || [], function (i, opt) {
					$select.append($('<option></option>').attr('value', opt.value).text(opt.label));
				});
				$select.val(value === null || value === undefined ? '' : String(value));
				$control.append($select);
				break;

			case 'checkbox':
				$control.append(
					$('<label class="bireme-checkbox"></label>')
						.append($('<input type="checkbox">').prop('checked', !!value))
						.append($('<span></span>').text(field.label))
				);
				break;

			case 'repeater':
				$control.append(renderRepeater(field, $.isArray(value) ? value : []));
				break;

			default:
				$control.append(
					$('<input type="text" class="widefat">').attr('placeholder', field.placeholder || '').val(value || '')
				);
		}

		$field.append($control);

		if (field.help) {
			$field.append($('<p class="description"></p>').text(field.help));
		}

		return $field;
	}

	function renderImage(value) {
		value = $.extend({ id: 0, url: '', alt: '' }, value || {});

		var $preview = $('<div class="bireme-image__preview"></div>');
		if (value.url) {
			$preview.append($('<img alt="">').attr('src', value.url));
		}

		return $('<div class="bireme-image"></div>')
			.append($('<input type="hidden" class="bireme-image__id">').val(value.id || ''))
			.append($('<input type="hidden" class="bireme-image__url">').val(value.url || ''))
			.append($('<input type="hidden" class="bireme-image__alt">').val(value.alt || ''))
			.append($preview)
			.append(
				$('<div class="bireme-image__actions"></div>')
					.append($('<button type="button" class="button bireme-image__select"></button>').text(t(value.url ? 'changeImage' : 'selectImage')))
					.append($('<button type="button" class="button-link bireme-image__remove"></button>').text(t('removeImage')).prop('hidden', !value.url))
			);
	}

	function renderLink(value) {
		value = $.extend({ url: '', text: '', target: '' }, value || {});

		// type="text" (e não "url") para aceitar /caminho e #ancora sem bloquear o submit do post.
		return $('<div class="bireme-link"></div>')
			.append($('<input type="text" class="bireme-link__text">').attr('placeholder', t('linkText')).val(value.text))
			.append($('<input type="text" class="bireme-link__url">').attr('placeholder', t('linkUrl')).val(value.url))
			.append(
				$('<label class="bireme-checkbox"></label>')
					.append($('<input type="checkbox" class="bireme-link__target">').prop('checked', value.target === '_blank'))
					.append($('<span></span>').text(t('newTab')))
			);
	}

	function renderRepeater(field, rows) {
		var $rows = $('<div class="bireme-repeater__rows"></div>');
		$.each(rows, function (i, row) {
			$rows.append(renderRow(field, row, false));
		});

		return $('<div class="bireme-repeater"></div>')
			.data('field', field)
			.append($rows)
			.append($('<button type="button" class="button bireme-repeater__add"></button>').text(field.button_label || t('addItem')));
	}

	function renderRow(field, data, expanded) {
		var $header = $('<div class="bireme-row__header"></div>')
			.append($('<span class="bireme-row__handle dashicons dashicons-menu" aria-hidden="true"></span>').attr('title', t('drag')))
			.append('<span class="bireme-row__title"></span>')
			.append(iconButton('bireme-row__remove', 'dashicons-trash', t('remove')))
			.append(iconButton('bireme-row__toggle', 'dashicons-arrow-down-alt2', t('toggle')));

		return $('<div class="bireme-row"></div>')
			.toggleClass('is-collapsed', !expanded)
			.append($header)
			.append($('<div class="bireme-row__body"></div>').append(renderFields(field.fields, data)));
	}

	function refreshRepeater($rep) {
		var field = $rep.data('field');
		if (!field) {
			return;
		}
		var $rows = $rep.children('.bireme-repeater__rows').children('.bireme-row');

		$rows.each(function (i) {
			var label = t('item') + ' ' + (i + 1);
			if (field.row_label) {
				var text = $(this)
					.children('.bireme-row__body')
					.children('.bireme-fields')
					.children('.bireme-field[data-key="' + field.row_label + '"]')
					.find('.bireme-field__control input[type="text"], .bireme-field__control textarea')
					.first()
					.val();
				if (text) {
					label += ': ' + text;
				}
			}
			$(this).children('.bireme-row__header').children('.bireme-row__title').text(label);
		});

		$rep.children('.bireme-repeater__add').prop('disabled', field.max > 0 && $rows.length >= field.max);
	}

	/* ------------------------------------------------------------------ *
	 * Cards de seção
	 * ------------------------------------------------------------------ */

	function buildCard(item, expanded) {
		var section = sections[item.type];
		var enabled = item.enabled !== false;

		var $title = $('<span class="bireme-card__title"></span>')
			.append($('<strong></strong>').text(section.label))
			.append('<span class="bireme-card__summary"></span>')
			.append($('<span class="bireme-card__badge"></span>').text(t('hidden')));

		var $header = $('<div class="bireme-card__header"></div>')
			.append($('<span class="bireme-card__handle dashicons dashicons-move" aria-hidden="true"></span>').attr('title', t('drag')))
			.append($('<span class="bireme-card__icon dashicons" aria-hidden="true"></span>').addClass(section.icon))
			.append($title)
			.append(
				$('<span class="bireme-card__actions"></span>')
					.append(iconButton('bireme-card__enable', enabled ? 'dashicons-visibility' : 'dashicons-hidden', t('toggleVisibility')))
					.append(iconButton('bireme-card__duplicate', 'dashicons-admin-page', t('duplicate')))
					.append(iconButton('bireme-card__remove', 'dashicons-trash', t('remove')))
					.append(iconButton('bireme-card__toggle', 'dashicons-arrow-down-alt2', t('toggle')))
			);

		var data = $.extend(defaultsFor(section.fields), item.data || {});
		var $body = $('<div class="bireme-card__body"></div>').append(renderFields(section.fields, data));

		if (section.container) {
			$body.append(buildColumns(data.layout, item.columns || []));
		}

		return $('<div class="bireme-card"></div>')
			.attr({ 'data-id': item.id || uid(), 'data-type': item.type })
			.toggleClass('is-container', !!section.container)
			.toggleClass('is-disabled', !enabled)
			.toggleClass('is-collapsed', !expanded)
			.append($header)
			.append($body);
	}

	/* ------------------------------------------------------------------ *
	 * Containers (seção "Colunas"): o campo 'layout' ("75-25") define as colunas
	 * ------------------------------------------------------------------ */

	function columnCount(layout) {
		return String(layout || '').split('-').filter(function (part) {
			return part !== '';
		}).length || 1;
	}

	function columnLabel(layout, index) {
		var parts = String(layout || '').split('-');
		return t('column') + ' ' + (index + 1) + (parts[index] ? ' (' + parts[index] + '%)' : '');
	}

	function buildColumn(layout, index, children) {
		var $items = $('<div class="bireme-column__items"></div>').attr('data-empty', t('dropHere'));
		$.each(children || [], function (i, child) {
			if (child && sections[child.type] && !sections[child.type].container) {
				$items.append(buildCard(child, false));
			}
		});
		return $('<div class="bireme-column"></div>')
			.append($('<div class="bireme-column__label"></div>').text(columnLabel(layout, index)))
			.append($items);
	}

	function buildColumns(layout, columns) {
		var count = columnCount(layout);
		var $wrap = $('<div class="bireme-columns"></div>');
		for (var i = 0; i < count; i++) {
			var children = columns[i] ? columns[i].slice() : [];
			if (i === count - 1) {
				for (var j = count; j < columns.length; j++) {
					children = children.concat(columns[j] || []);
				}
			}
			$wrap.append(buildColumn(layout, i, children));
		}
		return $wrap;
	}

	/** Ajusta o número de colunas após trocar o layout; seções de colunas removidas vão para a última. */
	function syncColumns($card) {
		var layout = $card.find('> .bireme-card__body > .bireme-fields > .bireme-field[data-key="layout"] select').val();
		var count = columnCount(layout);
		var $wrap = $card.find('> .bireme-card__body > .bireme-columns');
		var $cols = $wrap.children('.bireme-column');
		var i;

		for (i = $cols.length; i < count; i++) {
			var $col = buildColumn(layout, i, []);
			$wrap.append($col);
			initPlugins($col);
		}

		if ($cols.length > count) {
			var $target = $cols.eq(count - 1).children('.bireme-column__items');
			$cols.slice(count).each(function () {
				var $moving = $(this).children('.bireme-column__items').children('.bireme-card');
				destroyEditors($moving);
				$target.append($moving);
				$(this).remove();
			});
			initEditors($target);
		}

		$wrap.children('.bireme-column').each(function (index) {
			$(this).children('.bireme-column__label').text(columnLabel(layout, index));
		});

		serialize();
	}

	function reassignIds(item) {
		item.id = uid();
		$.each(item.columns || [], function (i, column) {
			$.each(column || [], function (j, child) {
				reassignIds(child);
			});
		});
		return item;
	}

	function cardSortableOptions(connectWith) {
		return {
			handle: '> .bireme-card__header > .bireme-card__handle',
			connectWith: connectWith,
			placeholder: 'bireme-card-placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			start: function (e, ui) {
				destroyEditors(ui.item);
			},
			receive: function (e, ui) {
				// Containers não podem ficar dentro de colunas.
				if (ui.item.hasClass('is-container') && $(this).hasClass('bireme-column__items') && ui.sender && ui.sender.hasClass('ui-sortable')) {
					ui.sender.sortable('cancel');
				}
			},
			update: handleDrops,
			stop: function (e, ui) {
				handleDrops();
				if (ui.item.hasClass('bireme-card')) {
					initEditors(ui.item);
				}
				serialize();
			}
		};
	}

	function updateSummary($card) {
		var section = sections[$card.attr('data-type')];
		var key = null;
		$.each(section ? section.fields : [], function (i, field) {
			if (field.type === 'text' && !field.condition) {
				key = field.key;
				return false;
			}
		});

		var text = '';
		if (key) {
			text = $card
				.children('.bireme-card__body')
				.children('.bireme-fields')
				.children('.bireme-field[data-key="' + key + '"]')
				.find('input')
				.first()
				.val() || '';
		}
		$card.find('> .bireme-card__header .bireme-card__summary').text(text ? '— ' + text : '');
	}

	function addSection(type, $replace) {
		if (!sections[type]) {
			return;
		}
		var $card = buildCard({ id: uid(), type: type, enabled: true, data: defaultsFor(sections[type].fields) }, true);

		if ($replace && $replace.length) {
			$replace.replaceWith($card);
		} else {
			$canvas.append($card);
		}

		activate($card);
		serialize();
	}

	/** Troca os itens soltos da biblioteca por cards de verdade. */
	function handleDrops() {
		$canvas.find('.bireme-library__item').each(function () {
			addSection($(this).attr('data-type'), $(this));
		});
	}

	function toggleCollapsed($el) {
		var collapsed = !$el.hasClass('is-collapsed');
		$el.toggleClass('is-collapsed', collapsed);
		if (!collapsed) {
			initEditors($el);
		}
	}

	/* ------------------------------------------------------------------ *
	 * Serialização
	 * ------------------------------------------------------------------ */

	function readFields($wrap, fields) {
		var data = {};
		$.each(fields || [], function (i, field) {
			var $field = $wrap.children('.bireme-field[data-key="' + field.key + '"]');
			if ($field.length) {
				data[field.key] = readField($field, field);
			}
		});
		return data;
	}

	function readField($field, field) {
		var $control = $field.children('.bireme-field__control');

		switch (field.type) {
			case 'wysiwyg':
				return editorContent($control.find('textarea.bireme-wysiwyg').attr('id'));

			case 'image':
				return {
					id: parseInt($control.find('.bireme-image__id').val(), 10) || 0,
					url: $control.find('.bireme-image__url').val() || '',
					alt: $control.find('.bireme-image__alt').val() || ''
				};

			case 'link':
				return {
					url: $control.find('.bireme-link__url').val() || '',
					text: $control.find('.bireme-link__text').val() || '',
					target: $control.find('.bireme-link__target').is(':checked') ? '_blank' : ''
				};

			case 'checkbox':
				return $control.find('input[type="checkbox"]').is(':checked');

			case 'color':
				return $control.find('input.bireme-color').val() || '';

			case 'select':
				return $control.find('select').val() || '';

			case 'textarea':
				return $control.find('textarea').val() || '';

			case 'repeater':
				var rows = [];
				$control.find('> .bireme-repeater > .bireme-repeater__rows > .bireme-row').each(function () {
					rows.push(readFields($(this).children('.bireme-row__body').children('.bireme-fields'), field.fields));
				});
				return rows;

			default:
				return $control.find('input').first().val() || '';
		}
	}

	function readCard($card) {
		var section = sections[$card.attr('data-type')];
		var item = {
			id: $card.attr('data-id'),
			type: $card.attr('data-type'),
			enabled: !$card.hasClass('is-disabled'),
			data: readFields($card.children('.bireme-card__body').children('.bireme-fields'), section.fields)
		};

		if (section.container) {
			item.columns = [];
			$card.find('> .bireme-card__body > .bireme-columns > .bireme-column').each(function () {
				var column = [];
				$(this).children('.bireme-column__items').children('.bireme-card').each(function () {
					column.push(readCard($(this)));
				});
				item.columns.push(column);
			});
		}

		return item;
	}

	function serialize() {
		clearTimeout(serializeTimer);
		var items = [];
		$canvas.children('.bireme-card').each(function () {
			items.push(readCard($(this)));
		});
		$input.val(JSON.stringify(items));
		$root.toggleClass('is-empty', items.length === 0);
	}

	function scheduleSerialize() {
		clearTimeout(serializeTimer);
		serializeTimer = setTimeout(serialize, 300);
	}

	/* ------------------------------------------------------------------ *
	 * Eventos
	 * ------------------------------------------------------------------ */

	function bindEvents() {
		$root.on('click', '.bireme-library__add', function (e) {
			e.preventDefault();
			var $card;
			addSection($(this).closest('.bireme-library__item').attr('data-type'));
			$card = $canvas.children('.bireme-card').last();
			if ($card.length && $card[0].scrollIntoView) {
				$card[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			}
		});

		$root.on('click', '.bireme-card__toggle, .bireme-card__title', function () {
			toggleCollapsed($(this).closest('.bireme-card'));
		});

		$root.on('click', '.bireme-card__enable', function () {
			var $card = $(this).closest('.bireme-card');
			var disabled = !$card.hasClass('is-disabled');
			$card.toggleClass('is-disabled', disabled);
			$(this).find('.dashicons').toggleClass('dashicons-visibility', !disabled).toggleClass('dashicons-hidden', disabled);
			serialize();
		});

		$root.on('click', '.bireme-card__duplicate', function () {
			var $card = $(this).closest('.bireme-card');
			var item = reassignIds(readCard($card));
			var $copy = buildCard(item, true);
			$card.after($copy);
			activate($copy);
			serialize();
		});

		$root.on('click', '.bireme-card__remove', function () {
			if (!window.confirm(t('confirmRemove'))) {
				return;
			}
			var $card = $(this).closest('.bireme-card');
			destroyEditors($card);
			$card.remove();
			serialize();
		});

		$root.on('click', '.bireme-row__toggle, .bireme-row__title', function () {
			toggleCollapsed($(this).closest('.bireme-row'));
		});

		$root.on('click', '.bireme-row__remove', function () {
			if (!window.confirm(t('confirmRemoveRow'))) {
				return;
			}
			var $row = $(this).closest('.bireme-row');
			var $rep = $row.closest('.bireme-repeater');
			destroyEditors($row);
			$row.remove();
			refreshRepeater($rep);
			serialize();
		});

		$root.on('click', '.bireme-repeater__add', function () {
			var $rep = $(this).closest('.bireme-repeater');
			var field = $rep.data('field');
			var $row = renderRow(field, defaultsFor(field.fields), true);
			$rep.children('.bireme-repeater__rows').append($row);
			activate($row);
			refreshRepeater($rep);
			serialize();
		});

		$root.on('click', '.bireme-image__select', function (e) {
			e.preventDefault();
			if (!window.wp || !wp.media) {
				return;
			}
			var $box = $(this).closest('.bireme-image');
			var frame = wp.media({
				title: t('selectImage'),
				button: { text: t('useImage') },
				library: { type: 'image' },
				multiple: false
			});
			frame.on('select', function () {
				var a = frame.state().get('selection').first().toJSON();
				var preview = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
				$box.find('.bireme-image__id').val(a.id);
				$box.find('.bireme-image__url').val(a.url);
				$box.find('.bireme-image__alt').val(a.alt || '');
				$box.find('.bireme-image__preview').empty().append($('<img alt="">').attr('src', preview));
				$box.find('.bireme-image__select').text(t('changeImage'));
				$box.find('.bireme-image__remove').prop('hidden', false);
				serialize();
			});
			frame.open();
		});

		$root.on('click', '.bireme-image__remove', function (e) {
			e.preventDefault();
			var $box = $(this).closest('.bireme-image');
			$box.find('.bireme-image__id, .bireme-image__url, .bireme-image__alt').val('');
			$box.find('.bireme-image__preview').empty();
			$box.find('.bireme-image__select').text(t('selectImage'));
			$(this).prop('hidden', true);
			serialize();
		});

		$root.on('input change', '.bireme-canvas input, .bireme-canvas textarea, .bireme-canvas select', function (e) {
			var $rep = $(this).closest('.bireme-repeater');
			if ($rep.length) {
				refreshRepeater($rep);
			}
			if (e.type === 'change') {
				var $fieldsWrap = $(this).closest('.bireme-field').parent('.bireme-fields');
				applyConditions($fieldsWrap);

				var $owner = $fieldsWrap.parent('.bireme-card__body').parent('.bireme-card');
				if ($owner.hasClass('is-container') && $(this).closest('.bireme-field').attr('data-key') === 'layout') {
					syncColumns($owner);
				}
			}
			updateSummary($(this).closest('.bireme-card'));
			scheduleSerialize();
		});

		$('#post').on('submit', serialize);
	}

	/* ------------------------------------------------------------------ *
	 * Inicialização
	 * ------------------------------------------------------------------ */

	$(function () {
		$root = $('#bireme-builder');
		if (!$root.length) {
			return;
		}
		$canvas = $root.find('.bireme-canvas');
		$input = $('#bireme-builder-data');

		var items = [];
		try {
			items = JSON.parse($input.val() || '[]') || [];
		} catch (err) {
			items = [];
		}

		$.each(items, function (i, item) {
			if (item && sections[item.type]) {
				$canvas.append(buildCard(item, false));
			}
		});
		activate($canvas);

		$canvas.sortable(cardSortableOptions('.bireme-column__items'));

		$root.find('.bireme-library__item').each(function () {
			var section = sections[$(this).attr('data-type')];
			$(this).draggable({
				// Containers só entram no nível raiz; as demais seções também entram nas colunas.
				connectToSortable: section && section.container ? '.bireme-canvas' : '.bireme-canvas, .bireme-column__items',
				helper: function () {
					return $(this).clone().addClass('is-helper').width($(this).outerWidth());
				},
				appendTo: 'body',
				revert: 'invalid',
				cursor: 'move',
				zIndex: 100000
			});
		});

		bindEvents();
		serialize();
	});
})(jQuery);
