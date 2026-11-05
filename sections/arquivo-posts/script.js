(function () {
	'use strict';

	function init(root) {
		var section = root.closest('.bireme-section');
		var grid = root.querySelector('[data-bb-archive-grid]');
		if (!section || !section.id || !grid || !window.fetch || !window.DOMParser) {
			return;
		}

		root.addEventListener('click', function (e) {
			var button = e.target.closest('[data-bb-archive-more]');
			if (!button || button.classList.contains('is-loading')) {
				return;
			}
			e.preventDefault();

			var label = button.textContent;
			button.classList.add('is-loading');
			button.setAttribute('aria-busy', 'true');
			button.textContent = button.getAttribute('data-loading') || label;

			fetch(button.href, { credentials: 'same-origin' })
				.then(function (response) {
					if (!response.ok) {
						throw new Error(response.status);
					}
					return response.text();
				})
				.then(function (html) {
					var doc = new DOMParser().parseFromString(html, 'text/html');
					var remote = doc.getElementById(section.id);
					if (!remote) {
						throw new Error('section');
					}

					var first = null;
					Array.prototype.forEach.call(remote.querySelectorAll('.bb-ap__card'), function (card) {
						card.classList.remove('bb-ap__card--featured');
						card.classList.add('is-new');
						grid.appendChild(document.importNode(card, true));
						first = first || grid.lastElementChild;
					});

					var pagination = root.querySelector('[data-bb-archive-pagination]');
					var remotePagination = remote.querySelector('[data-bb-archive-pagination]');
					if (pagination && remotePagination) {
						pagination.innerHTML = remotePagination.innerHTML;
					}

					var remoteButton = remote.querySelector('[data-bb-archive-more]');
					if (remoteButton) {
						button.href = remoteButton.href;
						button.textContent = label;
						button.classList.remove('is-loading');
						button.removeAttribute('aria-busy');
					} else {
						button.remove();
					}

					if (first) {
						var link = first.querySelector('a');
						if (link) {
							link.focus({ preventScroll: true });
						}
					}
				})
				.catch(function () {
					window.location.href = button.href;
				});
		});
	}

	function boot() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-bb-archive]'), init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
