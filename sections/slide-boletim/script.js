(function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function SlideBoletim(root) {
		this.root = root;
		this.slides = root.querySelectorAll('.bb-sb__slide');
		this.dots = root.querySelectorAll('.bb-sb__dot');
		this.cards = root.querySelectorAll('.bb-sb__card');
		this.cardsWrap = root.querySelector('.bb-sb__cards');
		this.current = root.querySelector('[data-bb-current]');
		this.total = this.slides.length;
		this.index = 0;
		this.timer = null;
		this.paused = false;
		this.interval = parseInt(root.getAttribute('data-interval'), 10) || 6000;
		this.autoplay = root.getAttribute('data-autoplay') === '1' && !reduceMotion;

		if (this.total < 2) {
			return;
		}

		this.bind();
		this.start();
	}

	SlideBoletim.prototype.go = function (index, fromUser) {
		var i;
		index = (index + this.total) % this.total;
		this.index = index;

		for (i = 0; i < this.total; i++) {
			var active = i === index;
			this.slides[i].classList.toggle('is-active', active);
			if (active) {
				this.slides[i].removeAttribute('aria-hidden');
			} else {
				this.slides[i].setAttribute('aria-hidden', 'true');
			}
		}

		this.setActive(this.dots, index);
		this.setActive(this.cards, index);

		if (this.current) {
			this.current.textContent = String(index + 1);
		}

		this.revealCard(index);

		if (fromUser) {
			this.restart();
		}
	};

	SlideBoletim.prototype.setActive = function (list, index) {
		for (var i = 0; i < list.length; i++) {
			var active = i === index;
			list[i].classList.toggle('is-active', active);
			if (list[i].tagName === 'A') {
				continue;
			}
			if (active) {
				list[i].setAttribute('aria-current', 'true');
			} else {
				list[i].removeAttribute('aria-current');
			}
		}
	};

	/** Mantém o card ativo visível quando a lista rola na horizontal (mobile). */
	SlideBoletim.prototype.revealCard = function (index) {
		var wrap = this.cardsWrap;
		var card = this.cards[index];
		if (!wrap || !card || wrap.scrollWidth <= wrap.clientWidth) {
			return;
		}
		var left = card.offsetLeft;
		var right = left + card.offsetWidth;
		if (left < wrap.scrollLeft || right > wrap.scrollLeft + wrap.clientWidth) {
			wrap.scrollTo({ left: left - 8, behavior: reduceMotion ? 'auto' : 'smooth' });
		}
	};

	SlideBoletim.prototype.start = function () {
		var self = this;
		if (!this.autoplay || this.paused || this.timer) {
			return;
		}
		this.timer = window.setInterval(function () {
			self.go(self.index + 1, false);
		}, this.interval);
	};

	SlideBoletim.prototype.stop = function () {
		if (this.timer) {
			window.clearInterval(this.timer);
			this.timer = null;
		}
	};

	SlideBoletim.prototype.restart = function () {
		this.stop();
		this.start();
	};

	SlideBoletim.prototype.bind = function () {
		var self = this;
		var root = this.root;

		root.addEventListener('click', function (e) {
			var target = e.target.closest('[data-bb-go], [data-bb-prev], [data-bb-next]');
			if (!target || !root.contains(target)) {
				return;
			}
			if (target.hasAttribute('data-bb-prev')) {
				self.go(self.index - 1, true);
			} else if (target.hasAttribute('data-bb-next')) {
				self.go(self.index + 1, true);
			} else {
				self.go(parseInt(target.getAttribute('data-bb-go'), 10) || 0, true);
			}
		});

		root.addEventListener('mouseenter', function () {
			self.paused = true;
			self.stop();
		});
		root.addEventListener('mouseleave', function () {
			self.paused = false;
			self.start();
		});
		root.addEventListener('focusin', function () {
			self.paused = true;
			self.stop();
		});
		root.addEventListener('focusout', function (e) {
			if (!root.contains(e.relatedTarget)) {
				self.paused = false;
				self.start();
			}
		});

		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') {
				self.go(self.index - 1, true);
			} else if (e.key === 'ArrowRight') {
				self.go(self.index + 1, true);
			}
		});

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				self.stop();
			} else {
				self.start();
			}
		});

		var viewport = root.querySelector('.bb-sb__viewport');
		var startX = null;
		var startY = null;
		if (viewport) {
			viewport.addEventListener('touchstart', function (e) {
				startX = e.touches[0].clientX;
				startY = e.touches[0].clientY;
			}, { passive: true });
			viewport.addEventListener('touchend', function (e) {
				if (startX === null) {
					return;
				}
				var dx = e.changedTouches[0].clientX - startX;
				var dy = e.changedTouches[0].clientY - startY;
				startX = null;
				if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
					self.go(self.index + (dx < 0 ? 1 : -1), true);
				}
			}, { passive: true });
		}
	};

	function init() {
		var roots = document.querySelectorAll('[data-bb-slide-boletim]');
		for (var i = 0; i < roots.length; i++) {
			if (!roots[i].bbSlideBoletim) {
				roots[i].bbSlideBoletim = new SlideBoletim(roots[i]);
			}
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
