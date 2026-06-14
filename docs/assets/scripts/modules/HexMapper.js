export class HexMapper {

	static #HEX_PATTERN = /^[0-9A-F]+$/;

	static transform(rawValue) {

		if (!rawValue) return "AGUARDANDO INPUT...";

		const cleaned = this.#sanitize(rawValue);

		if (!this.#HEX_PATTERN.test(cleaned)) return "ID INVÁLIDO!";

		return cleaned;

	}

	static #sanitize(input) {
		return input.replace(/^0x/i, '').replace(/[- ]/g, '').toUpperCase();
	}

}
