export class SwaggerContext {

	static getBaseUrl() {

		const servers = window.ui?.specSelectors?.specJson()?.get('servers');

		if (!servers || servers.size === 0) return null;
		const firstUrl = servers.getIn([0, 'url']);
		if (!firstUrl) return null;
		return this.#normalizeUrl(firstUrl);

	}

	static #normalizeUrl(url) {
		return url.endsWith('/') ? url.slice(0, -1) : url;
	}

	static #toggleScreen(element, show) {

		if (!element) return;

		if (show) {
			element.classList.remove('core-ui-hidden');
			element.classList.add('core-ui-block');
			return;
		}

		element.classList.remove('core-ui-block');
		element.classList.add('core-ui-hidden');

	}

}