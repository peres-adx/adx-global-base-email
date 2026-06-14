import { DatabaseUiController } from './DatabaseUiController.js';

export class AutoAuthService {

	static captureTokenAndLogin() {

		const loginBlock = document.getElementById('operations-Auth-post_login');
		if (!loginBlock) return;

		const responseObserver = new MutationObserver((_, obs) => {

			const statusCodeEl = loginBlock.querySelector('.responses-table .response .response-col_status');
			if (!statusCodeEl) return;

			const statusCode = statusCodeEl.innerText.trim();

			if (!['200', '201'].includes(statusCode)) {
				obs.disconnect();
				return;
			}

			this.#extractTokenFromResponse(loginBlock, obs);

		});

		responseObserver.observe(loginBlock, { childList: true, subtree: true });

	}

	static applyAuthorization(token) {

		if (!window.ui) return;

		window.ui.preauthorizeApiKey("bearerAuth", token);
		DatabaseUiController.lock();

		this.#triggerAuthModal();
		this.#injectSuccessFeedback();

	}

	static revealAuthorizeButton() {

		const headerAuthBtn = this.#getHeaderAuthButton();
		if (!headerAuthBtn) return;

		headerAuthBtn.classList.add('core-display-block');
		requestAnimationFrame(() => headerAuthBtn.classList.add('core-animate-active'));

	}

	static purgeAuthorization() {

		if (!window.ui) return;

		const state					= window.ui.getState();
		const isAuthorized	= state.getIn(['auth', 'authorized', 'bearerAuth']);

		if (isAuthorized) window.ui.authActions.logout(["bearerAuth"]);

		DatabaseUiController.resetToZero();

	}

	static #extractTokenFromResponse(container, observer) {

		container.querySelectorAll('.hljs-attr').forEach(attr => {

			if (!attr.innerText.includes('accessToken')) return;

			const tokenSpan = attr.nextElementSibling?.nextElementSibling;
			const rawToken	= tokenSpan?.innerText.replace(/"/g, '');

			if (!rawToken || rawToken.length <= 50) return;

			navigator.clipboard.writeText(rawToken);
			this.applyAuthorization(rawToken);
			observer.disconnect();

		});

	}

	static #triggerAuthModal() {
		const triggerBtn = this.#getHeaderAuthButton() || document.querySelector('button.authorize');
		triggerBtn?.click();
	}

	static #injectSuccessFeedback() {

		const modalObserver = new MutationObserver((_, obs) => {

			const modal = document.querySelector('.modal-ux');
			if (!modal || document.getElementById('core-success-msg')) return;

			const modalTitle	= modal.querySelector('.modal-ux-header h3');
			const successMsg	= document.createElement('div');

			successMsg.id					= 'core-success-msg';
			successMsg.className	= 'core-auth-alert-success';
			successMsg.innerHTML	= `<strong>✔ Auto-Auth: Token aplicado!</strong><br>Ambiente selado. Clique em <strong>CLOSE</strong> para prosseguir.`;
			modalTitle?.parentElement.after(successMsg);

			modal.addEventListener('click', (e) => {
				const isNativeLogout = e.target.classList.contains('auth') && e.target.innerText.toUpperCase() === 'LOGOUT';
				if (isNativeLogout) DatabaseUiController.terminateSession();
			});

			DatabaseUiController.renderLogoutButton();
			obs.disconnect();

		});

		modalObserver.observe(document.body, { childList: true, subtree: true });

	}

	static #getHeaderAuthButton() {
		const allAuthButtons = document.querySelectorAll('button.authorize');
		return Array.from(allAuthButtons).find(btn => !btn.closest('.opblock'));
	}

}