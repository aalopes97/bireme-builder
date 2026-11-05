(function () {
	'use strict';

	function setMessage(form, text, type) {
		var box = form.querySelector('.bb-nl__message');
		if (!box) {
			return;
		}
		box.textContent = text || '';
		box.classList.remove('is-success', 'is-error');
		if (type) {
			box.classList.add('is-' + type);
		}
	}

	function init(form) {
		var messages = {};
		try {
			messages = JSON.parse(form.getAttribute('data-messages') || '{}');
		} catch (e) {
			messages = {};
		}

		form.addEventListener('submit', function (e) {
			if (!window.fetch) {
				return;
			}
			e.preventDefault();

			var email = form.querySelector('input[type="email"]');
			var honeypot = form.querySelector('input[name="website"]');
			var lang = form.querySelector('input[name="lang"]');
			var button = form.querySelector('button[type="submit"]');

			if (!email.value || !email.checkValidity()) {
				setMessage(form, messages.invalid, 'error');
				email.focus();
				return;
			}

			button.disabled = true;
			form.classList.add('is-loading');

			fetch(form.getAttribute('data-endpoint'), {
				method: 'POST',
				credentials: 'omit',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					email: email.value,
					website: honeypot ? honeypot.value : '',
					lang: lang ? lang.value : ''
				})
			})
				.then(function (response) {
					return response.json().catch(function () {
						return { status: 'error' };
					});
				})
				.then(function (result) {
					var status = result && result.status ? result.status : 'error';
					setMessage(form, messages[status] || messages.error, status === 'ok' ? 'success' : 'error');
					if (status === 'ok') {
						form.reset();
					}
				})
				.catch(function () {
					setMessage(form, messages.error, 'error');
				})
				.then(function () {
					button.disabled = false;
					form.classList.remove('is-loading');
				});
		});
	}

	function boot() {
		var forms = document.querySelectorAll('form[data-bb-newsletter]');
		for (var i = 0; i < forms.length; i++) {
			init(forms[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
