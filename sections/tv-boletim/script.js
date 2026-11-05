(function () {
	'use strict';

	function pad(n) {
		return n < 10 ? '0' + n : String(n);
	}

	function formatDate(now, lang) {
		try {
			return now.toLocaleDateString(lang || undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
		} catch (e) {
			return now.toLocaleDateString();
		}
	}

	function startClock(clock, dateEl, lang) {
		var hm = clock ? clock.querySelector('.bb-tv__clock-hm') : null;
		var s = clock ? clock.querySelector('.bb-tv__clock-s') : null;
		var lastDay = -1;

		function tick() {
			var now = new Date();
			if (clock) {
				if (hm && s) {
					hm.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes());
					s.textContent = ':' + pad(now.getSeconds());
				} else {
					clock.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
				}
				clock.setAttribute('datetime', now.toISOString());
			}
			if (dateEl && now.getDate() !== lastDay) {
				lastDay = now.getDate();
				dateEl.textContent = formatDate(now, lang);
			}
		}
		tick();
		window.setInterval(tick, 1000);
	}

	function init(tv) {
		var slides = tv.querySelectorAll('[data-bb-tv-slide]');
		var items = tv.querySelectorAll('[data-bb-tv-item]');
		var segs = tv.querySelectorAll('[data-bb-tv-seg]');
		var list = tv.querySelector('[data-bb-tv-list]');
		var qr = tv.querySelector('[data-bb-tv-qr][data-dynamic]');
		var indexEl = tv.querySelector('[data-bb-tv-index]');
		var clock = tv.querySelector('[data-bb-tv-clock]');
		var dateEl = tv.querySelector('[data-bb-tv-date]');
		var interval = Math.max(3, parseInt(tv.getAttribute('data-interval'), 10) || 12) * 1000;
		var refresh = parseInt(tv.getAttribute('data-refresh'), 10) || 0;
		var nextLang = tv.getAttribute('data-next-lang') || '';
		var rotate = tv.getAttribute('data-rotate') || '';
		var current = 0;

		/* Abre a tela do próximo idioma; sem internet, tenta de novo em 1 minuto. */
		function goNextLang() {
			if (navigator.onLine === false) {
				window.setTimeout(goNextLang, 60000);
				return;
			}
			window.location.href = nextLang;
		}

		tv.style.setProperty('--bb-tv-duration', interval / 1000 + 's');

		if (clock || dateEl) {
			startClock(clock, dateEl, tv.getAttribute('data-lang'));
		}

		function updateSegments(index) {
			Array.prototype.forEach.call(segs, function (seg, i) {
				seg.classList.remove('is-active');
				seg.classList.toggle('is-done', i < index);
			});
			if (segs[index]) {
				void segs[index].offsetWidth;
				segs[index].classList.add('is-active');
			}
		}

		/* A notícia atual fica no topo da lista enquanto houver itens abaixo para preencher a área. */
		function scrollList(index) {
			if (!list || !items[index]) {
				return;
			}
			var wrap = list.parentElement;
			var maxOffset = Math.max(0, list.scrollHeight - wrap.clientHeight);
			var offset = Math.min(items[index].offsetTop - list.offsetTop, maxOffset);
			list.style.transform = 'translateY(' + -offset + 'px)';
		}

		function show(index) {
			slides[current].classList.remove('is-active');
			slides[current].setAttribute('aria-hidden', 'true');
			if (items[current]) {
				items[current].classList.remove('is-active');
			}

			current = index;

			slides[current].classList.add('is-active');
			slides[current].removeAttribute('aria-hidden');
			if (items[current]) {
				items[current].classList.add('is-active');
			}

			var lazy = slides[(current + 1) % slides.length].querySelector('img[loading="lazy"]');
			if (lazy) {
				lazy.removeAttribute('loading');
			}

			if (qr) {
				var src = slides[current].getAttribute('data-qr');
				if (src) {
					qr.src = src;
				}
			}

			if (indexEl) {
				indexEl.textContent = pad(current + 1);
			}

			scrollList(current);
			updateSegments(current);
		}

		scrollList(0);

		var cycleLang = nextLang && rotate === 'cycle';

		if (slides.length > 1) {
			updateSegments(0);
			window.setInterval(function () {
				/* Sem internet, continua exibindo as notícias e tenta de novo no fim da próxima volta. */
				if (cycleLang && current === slides.length - 1 && navigator.onLine !== false) {
					window.location.href = nextLang;
					return;
				}
				show((current + 1) % slides.length);
			}, interval);
		} else if (cycleLang) {
			window.setTimeout(goNextLang, interval);
		}

		if (nextLang && !cycleLang) {
			window.setTimeout(goNextLang, (parseInt(rotate, 10) || 10) * 60000);
		} else if (refresh > 0 && !nextLang) {
			window.setTimeout(function reload() {
				if (navigator.onLine === false) {
					window.setTimeout(reload, 60000);
					return;
				}
				window.location.reload();
			}, refresh * 60000);
		}
	}

	function boot() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-bb-tv]'), init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
