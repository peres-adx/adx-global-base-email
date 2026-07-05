export class TemplateLoader {

	static #cache = null;
	static #PATH = './components.html';

	static async get(templateId) {
		const htmlText = await this.#fetchComponentsFile();
		if (!htmlText) return null;

		const parser = new DOMParser();
		const doc = parser.parseFromString(htmlText, 'text/html');
		const template = doc.getElementById(templateId);
		if (!template) return null;

		return template.content.cloneNode(true);
	}

	static async #fetchComponentsFile() {
		if (this.#cache) return this.#cache;

		const res = await fetch(this.#PATH);
		if (!res.ok) return null;

		this.#cache = await res.text();
		return this.#cache;
	}

}