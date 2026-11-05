(function () {
	'use strict';

	var synth = window.speechSynthesis;

	/** Divide o texto em trechos curtos: alguns navegadores interrompem falas longas. */
	function chunks(text, max) {
		var sentences = text.replace(/\s+/g, ' ').match(/[^.!?…;:]+[.!?…;:]*\s*/g) || [];
		var out = [];
		var current = '';
		sentences.forEach(function (sentence) {
			if ((current + sentence).length > max && current) {
				out.push(current.trim());
				current = '';
			}
			current += sentence;
		});
		if (current.trim()) {
			out.push(current.trim());
		}
		return out;
	}

	function pickVoice(lang) {
		var voices = synth.getVoices();
		var prefix = lang.toLowerCase().split('-')[0];
		var exact = null;
		var partial = null;
		voices.forEach(function (voice) {
			var vlang = voice.lang.toLowerCase().replace('_', '-');
			if (!exact && vlang === lang.toLowerCase()) {
				exact = voice;
			}
			if (!partial && vlang.split('-')[0] === prefix) {
				partial = voice;
			}
		});
		return exact || partial;
	}

	function init(article) {
		var wrap = article.querySelector('[data-bb-listen]');
		if (!wrap || !synth || !window.SpeechSynthesisUtterance) {
			return;
		}

		var toggle = wrap.querySelector('[data-bb-listen-toggle]');
		var stopBtn = wrap.querySelector('[data-bb-listen-stop]');
		var label = wrap.querySelector('[data-bb-listen-label]');
		var lang = document.documentElement.lang || 'pt-BR';
		var state = 'idle';
		var queue = [];
		var position = 0;

		wrap.hidden = false;

		function setState(next) {
			state = next;
			var key = next === 'playing' ? 'data-label-pause' : (next === 'paused' ? 'data-label-resume' : 'data-label-play');
			label.textContent = toggle.getAttribute(key);
			toggle.setAttribute('aria-pressed', next === 'playing' ? 'true' : 'false');
			stopBtn.hidden = next === 'idle';
			wrap.classList.toggle('is-active', next !== 'idle');
		}

		function speakNext() {
			if (state === 'idle') {
				return;
			}
			if (position >= queue.length) {
				setState('idle');
				return;
			}
			var utterance = new window.SpeechSynthesisUtterance(queue[position]);
			var voice = pickVoice(lang);
			utterance.lang = lang;
			if (voice) {
				utterance.voice = voice;
			}
			utterance.onend = function () {
				position++;
				speakNext();
			};
			utterance.onerror = function (e) {
				if (e.error !== 'interrupted' && e.error !== 'canceled') {
					setState('idle');
				}
			};
			synth.speak(utterance);
		}

		function start() {
			var title = article.querySelector('[data-bb-post-title]');
			var content = article.querySelector('[data-bb-post-content]');
			var text = (title ? title.innerText + '. ' : '') + (content ? content.innerText : '');
			queue = chunks(text, 220);
			position = 0;
			synth.cancel();
			setState('playing');
			speakNext();
		}

		function stop() {
			setState('idle');
			synth.cancel();
		}

		toggle.addEventListener('click', function () {
			if (state === 'idle') {
				start();
			} else if (state === 'playing') {
				synth.pause();
				setState('paused');
			} else {
				synth.resume();
				setState('playing');
			}
		});

		stopBtn.addEventListener('click', stop);
		window.addEventListener('pagehide', function () {
			synth.cancel();
		});
	}

	function boot() {
		if (synth) {
			synth.cancel();
		}
		Array.prototype.forEach.call(document.querySelectorAll('[data-bb-post]'), init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
