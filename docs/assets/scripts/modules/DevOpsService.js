import { SwaggerContext } from './SwaggerContext.js';

export class DevOpsService {

	#btn;
	#area;
	#msg;

	constructor() {
		this.#btn  = document.getElementById('btnRunAudit');
		this.#area = document.getElementById('auditResponseArea');
		this.#msg  = document.getElementById('auditMessage');
	}

	async runAudit() {

		const baseUrl = SwaggerContext.getBaseUrl();
		if (!baseUrl) return this.#renderFeedback('Falha de comunicação com o servidor.', '#FF5C5C');

		this.#setLoadingState(true);

		const path = '/api/tests/audit';

		const response = await this.#fetchWithFallback(baseUrl, path);
		if (!response) {
			this.#setLoadingState(false);
			return this.#renderFeedback('Servidor indisponível ou erro crítico de rede.', '#FF5C5C');
		}

		if (!response.ok) {
			this.#setLoadingState(false);
			return this.#renderFeedback(`Falha de comunicação ou erro ${response.status} na API.`, '#FF5C5C');
		}

		const payload = await response.json();
		this.#setLoadingState(false);

		const isSuccess = payload.status === 200 || payload.status === 'success' || payload.code === 200;
		if (!isSuccess) return this.#renderFeedback(`Erro no Pipeline: ${payload.detail || 'Falha desconhecida'}`, '#FF5C5C');

		const urlReport = payload.data?.report_url || payload.report_url;
		if (!urlReport) return this.#renderFeedback('✔ Auditoria processada, mas a URL do relatório não foi retornada.', '#72D11F');

		const reportLink = `<a href="${urlReport}" target="_blank" class="core-audit-link">Abrir Relatório ↗</a>`;
		return this.#renderFeedback(`✔ ${payload.detail} ${reportLink}`, '#72D11F');

	}

	async #fetchWithFallback(base, endpoint) {

		const cleanBase = base.endsWith('/') ? base.slice(0, -1) : base;
		const cleanEnd  = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
		const fullUrl   = `${cleanBase}${cleanEnd}`;

		try {
			const response = await fetch(fullUrl);
			if (!response.ok && response.status === 404) return await fetch(`${cleanBase}/index.php${cleanEnd}`);
			return response;
		} catch {
			return null;
		}

	}

	#setLoadingState(isLoading) {

		if (!this.#btn) return;

		this.#btn.disabled  = isLoading;
		this.#btn.innerText = isLoading ? 'Processando testes...' : 'Gerar Documento de Auditoria';
		this.#btn.classList.toggle('core-btn-loading', isLoading);

		if (isLoading && this.#area) this.#area.style.display = 'none';

	}

	#renderFeedback(html, color) {

		if (!this.#msg || !this.#area) return;

		this.#msg.style.color    = color;
		this.#msg.innerHTML       = html;
		this.#area.style.display  = 'block';

	}

}