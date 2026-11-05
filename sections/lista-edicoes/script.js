(function () {
	'use strict';

	function openYear(target) {
		if (target && target.tagName === 'DETAILS') {
			target.open = true;
		}
	}

	function init(root) {
		var links = root.querySelectorAll('[data-bb-ed-year]');
		var years = root.querySelectorAll('.bb-ed__year');

		Array.prototype.forEach.call(links, function (link) {
			link.addEventListener('click', function () {
				openYear(document.getElementById(link.getAttribute('href').slice(1)));
			});
		});

		if (window.location.hash.length > 1) {
			var hashTarget = document.getElementById(decodeURIComponent(window.location.hash.slice(1)));
			if (hashTarget && root.contains(hashTarget)) {
				openYear(hashTarget);
			}
		}

		if (!links.length || !('IntersectionObserver' in window)) {
			return;
		}

		/* Destaca na navegação o ano visível. */
		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}
				var id = entry.target.id;
				Array.prototype.forEach.call(links, function (link) {
					link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
				});
			});
		}, { rootMargin: '-20% 0px -70% 0px' });

		Array.prototype.forEach.call(years, function (year) {
			observer.observe(year);
		});
	}

	function boot() {
		Array.prototype.forEach.call(document.querySelectorAll('.bb-ed'), init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
