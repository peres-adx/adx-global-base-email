import { env } from '../config/env.js';
import { DatabaseUiController } from './DatabaseUiController.js';
import { TemplateLoader } from './TemplateLoader.js';

export class SetupPasswordEngine {

	static #URL_PARAM				= 'token';
	static #DB_PARAM				= 'db';
	static #SUCCESS_EVENT		= 'core:setup-success';

	static checkRouteAndIntercept() {

		const urlParams = new URLSearchParams(window.location.search);
		const token     = urlParams.get(this.#URL_PARAM);
		if (!token) return;

		this.#isolateSetupWorkspace();

		const swaggerEngineNames = {
			'mssql':  'SQL Server',
			'mysql':  'MySQL',
			'sqlite': 'SQLite'
		};

		const dbParam = urlParams.get(this.#DB_PARAM);
		if (!dbParam || !swaggerEngineNames[dbParam]) return this.#abortFlowWithSecurityError();

		window.addEventListener(this.#SUCCESS_EVENT, () => this.#promptSuccessAndRedirect());

		this.#bindSetupEvent(token, swaggerEngineNames[dbParam]);

	}

	static #abortFlowWithSecurityError() {

		const form = document.getElementById('formSetupReativo');
		if (!form) return;

		const rowInputs  = form.querySelector('.core-card-row');
		const rowActions = form.querySelector('.core-form-actions');

		if (rowInputs)  rowInputs.style.display  = 'none';
		if (rowActions) rowActions.style.display = 'none';

		const securityAlert = document.createElement('div');
		securityAlert.style.backgroundColor	= '#FFF5F5';
		securityAlert.style.border					= '1px solid #E13434';
		securityAlert.style.color						= '#E13434';
		securityAlert.style.padding					= '15px';
		securityAlert.style.borderRadius		= '4px';
		securityAlert.style.marginTop				= '15px';
		securityAlert.style.textAlign				= 'center';
		securityAlert.style.fontWeight			= 'bold';
		securityAlert.style.fontFamily			= 'monospace';
		securityAlert.style.fontSize				= '13px';
		
		securityAlert.innerHTML = `
			<strong>ERRO DE INTEGRIDADE:</strong> PARÂMETROS DE AMBIENTE INCOMPATÍVEIS.<br>
			A ativação da credencial foi abortada devido a modificações não autorizadas na assinatura da URL.
		`;

		form.appendChild(securityAlert);

	}

	static #isolateSetupWorkspace() {

		const welcome       = document.querySelector('.core-ui-section.core-welcome-screen');
		const setupSection  = document.querySelector('.core-ui-section.core-setup-password');

		DatabaseUiController.toggleScreen(welcome, false);
		DatabaseUiController.toggleScreen(setupSection, true);

		const selectObserver = new MutationObserver((_, obs) => {

			const dbSelect = document.querySelector('.swagger-ui select[data-variable="database"]');
			if (!dbSelect) return;

			dbSelect.disabled       = true;
			dbSelect.style.cursor   = 'not-allowed';
			dbSelect.style.opacity  = '0.6';

			obs.disconnect();

		});

		const swaggerObserver = new MutationObserver((_, obs) => {

			const infoBlock       = document.querySelector('.swagger-ui .info');
			const schemeContainer = document.querySelector('.swagger-ui .scheme-container');
			const wrapper         = document.querySelector('.swagger-ui .wrapper:not(.core-ui-section)');
			const authContainer   = document.querySelector('.core-auth-container') || document.querySelector('.swagger-ui .auth-wrapper');

			if (infoBlock)       infoBlock.style.display       = 'none';
			if (schemeContainer) schemeContainer.style.display = 'none';
			if (wrapper)         wrapper.style.display         = 'none';

			if (authContainer && !authContainer.querySelector('.btn-cancel')) {

				authContainer.innerHTML = `
					<button type="button" class="btn cancel" >
						CANCELAR
					</button>
				`;
				authContainer.querySelector('.core-btn-cancel')?.addEventListener('click', () => this.#purgeTokenAndReload());
				obs.disconnect();

			}

		});

		selectObserver.observe(document.body, { childList: true, subtree: true });
		swaggerObserver.observe(document.body, { childList: true, subtree: true });

	}

	static #bindSetupEvent(token, databaseEngine) {

		const form = document.getElementById('formSetupReativo');
		if (!form) return;

		const btnCancelSetup = document.getElementById('btnCancelSetup');
		if (btnCancelSetup) btnCancelSetup.onclick = () => this.#purgeTokenAndReload();

		form.onsubmit = async (e) => {

			e.preventDefault();
			e.stopPropagation();

			const password = document.getElementById('setup_password')?.value;
			const confirm  = document.getElementById('setup_confirm_password')?.value;

			if (password !== confirm) return this.#renderInternalModal('Atenção', 'As senhas informadas devem ser iguais nos dois campos.');
			if (password.length < 6)  return this.#renderInternalModal('Atenção', 'A senha deve conter no mínimo 6 caracteres.');

			this.#setButtonLoading(true);

			const { origin, pathname }  = window.location;
			const baseApiPath           = pathname.includes('/docs/') ? pathname.replace('/docs/', '/api/') : '/adx-global-base/api/';
			const endpoint              = `${origin}${baseApiPath.replace(/\/$/, '')}/api/users/setup-password`;

			const res = await fetch(endpoint, {
				method: 'POST',
				headers: { 
					'Content-Type':       'application/json',
					'X-Database-Engine':  databaseEngine
				},
				body: JSON.stringify({ 
					token:       token, 
					newPassword: password 
				})
			});

			const data = await res.json().catch(() => ({}));
			this.#setButtonLoading(false);

			if (!res.ok) return this.#renderInternalModal('Erro', data.detail || 'Falha ao processar ativação.');

			window.dispatchEvent(new CustomEvent(SetupPasswordEngine.#SUCCESS_EVENT));

		};

	}

	static async #promptSuccessAndRedirect() {

		const fragment = await TemplateLoader.get('core-template-success-modal');
		if (!fragment) return this.#purgeTokenAndReload();

		const wrapper     = fragment.querySelector('.core-modal-root');
		const box         = fragment.querySelector('.modal-ux');
		const btnRedirect = fragment.querySelector('.btn-modal-redirect');
		const closeBtn    = fragment.querySelector('.core-modal-close-btn');
		const iconBtn     = fragment.querySelector('.core-modal-close-icon');

		if (btnRedirect) {
			btnRedirect.onclick = () => {
				if (wrapper) wrapper.remove();
				SetupPasswordEngine.#purgeTokenAndReload();
			};
		}

		const purgeModal = () => {
			if (wrapper) wrapper.remove();
		};

		if (closeBtn) closeBtn.onclick = purgeModal;
		if (iconBtn)  iconBtn.onclick  = purgeModal;

		document.body.appendChild(fragment);

		requestAnimationFrame(() => {

			if (!wrapper || !box) return;

			wrapper.style.setProperty('background', 'rgba(0,0,0,0.6)', 'important');
			wrapper.style.setProperty('opacity', '1', 'important');
			box.style.setProperty('opacity', '1', 'important');
			box.style.setProperty('transform', 'translate(-50%, -50%)', 'important');

		});

	}

	static async #renderInternalModal(title, message) {

		const fragment = await TemplateLoader.get('core-template-internal-modal');
		if (!fragment) return alert(message);

		const wrapper  = fragment.querySelector('.core-modal-root');
		const box      = fragment.querySelector('.modal-ux');
		const closeBtn = fragment.querySelector('.core-modal-close-btn');
		const iconBtn  = fragment.querySelector('.core-modal-close-icon');

		const purgeModal = () => {
			if (wrapper) wrapper.remove();
		};

		if (closeBtn) closeBtn.onclick = purgeModal;
		if (iconBtn)  iconBtn.onclick  = purgeModal;

		document.body.appendChild(fragment);

		requestAnimationFrame(() => {

			if (!wrapper || !box) return;

			wrapper.style.setProperty('background', 'rgba(0,0,0,0.6)', 'important');
			wrapper.style.setProperty('opacity', '1', 'important');
			box.style.setProperty('opacity', '1', 'important');
			
			box.style.setProperty('transform', 'translate(-50%, -50%)', 'important');

		});

	}

	static #setButtonLoading(isLoading) {

		const btn = document.getElementById('btnSubmitSetup');
		if (!btn) return;

		btn.disabled = isLoading;
		isLoading ? btn.classList.add('core-btn-frozen') : btn.classList.remove('core-btn-frozen');

	}

	static #purgeTokenAndReload() {
		history.replaceState("", document.title, window.location.pathname);
		location.reload();
	}

}