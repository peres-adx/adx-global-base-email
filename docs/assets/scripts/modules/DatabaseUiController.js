export class DatabaseUiController {

	static lock() {

		const select = document.querySelector('.swagger-ui select[data-variable="database"]');
		if (!select) return;

		select.classList.add('core-engine-frozen');
		select.setAttribute('disabled', 'true');
		select.title = 'Encerre a sessão ativa para alterar a base de dados.';

	}

	static unlock() {

		const select = document.querySelector('.swagger-ui select[data-variable="database"]');
		if (!select) return;

		select.classList.remove('core-engine-frozen');
		select.removeAttribute('disabled');
		select.title = '';

		document.getElementById('core-btn-logout')?.remove();

	}

	static resetToZero() {

		this.unlock();

		const select = document.querySelector('.swagger-ui select[data-variable="database"]');
		if (select) select.value = "SELECIONE O BANCO DE DADOS";

		this.#resetCustomInputs();
		this.toggleWorkspace("SELECIONE O BANCO DE DADOS");

	}

	static toggleWorkspace(databaseValue) {

		const cleanedValue = databaseValue?.trim();
		const isDefault    = !cleanedValue || cleanedValue === "SELECIONE O BANCO DE DADOS";

		if (window.ui) {
			window.ui.getConfigs().requestInterceptor = (request) => {
				request.headers['X-Database-Engine'] = isDefault ? "" : cleanedValue;
				return request;
			};
		}

		this.#purgeSwaggerSystem();
		this.#handleUiTransition(isDefault);

	}

	static terminateSession() {

		this.#purgeSwaggerSystem();
		this.resetToZero();

		const closeSelectors = [
			'.swagger-ui .dialog-ux .close-modal',
			'.swagger-ui .btn-done',
			'.swagger-ui .modal-ux-header button'
		];

		closeSelectors.forEach(selector => {
			document.querySelector(selector)?.click();
		});

		window.scrollTo({ top: 0, behavior: 'smooth' });

	}

	static renderLogoutButton() {

		if (document.getElementById('core-btn-logout')) return;

		const observer = new MutationObserver((_, obs) => {

			const nativeWrapper	= document.querySelector('.swagger-ui .auth-wrapper');
			const nativeBtn			= nativeWrapper?.querySelector('button.authorize');

			if (!nativeBtn || document.getElementById('core-btn-logout')) return;

			const logoutBtn			= document.createElement('button');
			logoutBtn.id				= 'core-btn-logout';
			logoutBtn.className = 'btn core-logout-btn-premium';
			logoutBtn.innerText = 'Encerrar sessão';

			logoutBtn.onclick = (e) => {
				e.preventDefault();
				this.terminateSession();
			};

			nativeBtn.before(logoutBtn);
			obs.disconnect();

		});

		observer.observe(document.body, { childList: true, subtree: true });

	}

	static #purgeSwaggerSystem() {

		if (!window.ui) return;

		const { authActions, specActions, errActions }	= window.ui;
		const state																			= window.ui.getState();
		const authorized																= state.getIn(['auth', 'authorized']);

		if (authorized && authorized.size > 0) authActions.logout(Array.from(authorized.keys()));

		if (specActions?.changeOperationValue) {

			const spec	= state.get('spec');
			const paths	= spec.getIn(['json', 'paths']);

			if (paths) {
				paths.forEach((methods, path) => {
					methods.keySeq().forEach(method => {
						specActions.changeOperationValue([path, method], "isEditing", false);
					});
				});
			}

		}

		authActions?.showDefinitions?.(false);
		const clearErrors = specActions?.clearValidateErrors || errActions?.clearAllErrors;
		clearErrors?.();

		document.querySelectorAll('.swagger-ui .btn-group .cancel, .swagger-ui .try-out__btn.cancel').forEach(btn => btn.click());
		this.#collapseAllSections();

	}

	static #collapseAllSections() {

		const openBlocks = document.querySelectorAll('.swagger-ui .opblock.is-open');
		openBlocks.forEach(block => {
			const toggleBtn = block.querySelector('.opblock-summary-control') || block.querySelector('.opblock-summary');
			toggleBtn?.click();
		});

		const openTags = document.querySelectorAll('.swagger-ui .opblock-tag-section:not(.is-collapsed)');
		openTags.forEach(tagSection => {
			const tagBtn = tagSection.querySelector('button.opblock-tag');
			tagBtn?.click();
		});

	}

	static #handleUiTransition(isDefault) {

		const sections      = document.querySelectorAll('.swagger-ui section.block');
		const utilsSection	= document.querySelector('.core-panel-container');
		const welcomeScreen = document.querySelector('.core-welcome-screen');

		requestAnimationFrame(() => {

			if (isDefault) {
				sections.forEach(s => s.classList.remove('core-display-block', 'core-animate-active'));
				utilsSection?.classList.remove('core-display-block', 'core-animate-active');
				this.#toggleWelcome(welcomeScreen, true);
				return;
			}

			this.#toggleWelcome(welcomeScreen, false);
			sections.forEach(s => s.classList.add('core-display-block'));
			utilsSection?.classList.add('core-display-block');

			requestAnimationFrame(() => {
				sections.forEach(s => s.classList.add('core-animate-active'));
				utilsSection?.classList.add('core-animate-active');
			});

		});

	}

	static #toggleWelcome(element, show) {

		if (!element) return;

		if (show) {
			element.classList.remove('core-fade-out');
			element.style.display	= 'block';
			element.style.height	= 'auto';
			return;
		}

		element.classList.add('core-fade-out');
		element.style.height		= '0px';
		element.style.overflow	= 'hidden';

	}

	static #resetCustomInputs() {

		const binInput	= document.getElementById('binInput');
		const hexOutput	= document.getElementById('hexOutput');

		if (binInput)		binInput.value			= '';
		if (hexOutput)	hexOutput.innerText	= 'AGUARDANDO INPUT...';

	}

}
