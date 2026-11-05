/*! Bireme Builder | Desenvolvido por @toni - 2WP | https://2wp.com.br */
(function () {
	'use strict';

	function init() {
		var toggle = document.querySelector('.bireme-header__toggle');
		var nav = document.getElementById('bireme-header-nav');
		if (!toggle || !nav) {
			return;
		}

		function setOpen(open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			nav.classList.toggle('is-open', open);
		}

		toggle.addEventListener('click', function () {
			setOpen(toggle.getAttribute('aria-expanded') !== 'true');
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				setOpen(false);
				toggle.focus();
			}
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth > 860 && nav.classList.contains('is-open')) {
				setOpen(false);
			}
		});
	}

	var CHILDREN = [
		'.bb-ea__card',
		'.bb-ap__card',
		'.bb-pi__item',
		'.bb-td__item',
		'.bb-ml__item',
		'.bb-sb__card',
		'.bb-cols__col > .bireme-section'
	].join(',');

	function cleanup(section, children) {
		section.classList.remove('bb-reveal', 'is-visible');
		children.forEach(function (child) {
			child.classList.remove('bb-reveal-child');
			child.style.removeProperty('--bb-delay');
		});
	}

	/**
	 * Só esconde as seções que ainda estão abaixo da dobra no momento em que o script roda:
	 * o que já está na tela nunca pisca, e sem JS (ou com JS atrasado por cache) tudo fica visível.
	 */
	function initReveal() {
		var main = document.getElementById('bireme-main');
		if (!main || !('IntersectionObserver' in window)) {
			return;
		}
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}

		var sections = Array.prototype.filter.call(main.querySelectorAll('.bireme-section'), function (section) {
			return !section.parentElement.closest('.bireme-section');
		});

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}
				var section = entry.target;
				var children = Array.prototype.slice.call(section.querySelectorAll('.bb-reveal-child'));
				observer.unobserve(section);
				section.classList.add('is-visible');
				window.setTimeout(function () {
					cleanup(section, children);
				}, 1400);
			});
		}, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

		sections.forEach(function (section) {
			if (section.getBoundingClientRect().top < window.innerHeight) {
				return;
			}
			section.classList.add('bb-reveal');
			Array.prototype.forEach.call(section.querySelectorAll(CHILDREN), function (child, i) {
				child.classList.add('bb-reveal-child');
				child.style.setProperty('--bb-delay', (Math.min(i, 8) * 0.07).toFixed(2) + 's');
			});
			observer.observe(section);
		});
	}

	function boot() {
		init();
		initReveal();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
