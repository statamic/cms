import { pt as markRaw } from "./vue.esm-bundler-DZ9hA_XK.js";
// resources/js/components/field-actions/modal.js
function modal_default(props) {
	return new Promise((resolve) => {
		const component = Statamic.$components.append("field-action-modal", { props });
		const close = () => Statamic.$components.destroy(component.id);
		component.on("confirm", (data = {}) => {
			resolve({
				...data,
				confirmed: true
			});
			close();
		});
		component.on("cancel", () => {
			resolve({ confirmed: false });
			close();
		});
	});
}
// resources/js/components/field-actions/FieldAction.js
var FieldAction = class {
	#payload;
	#run;
	#disabled;
	#visible;
	#visibleWhenReadOnly;
	#icon;
	#quick;
	#dangerous;
	#confirm;
	constructor(action, payload) {
		this.#payload = payload;
		this.#run = action.run;
		this.#confirm = action.confirm;
		this.#disabled = action.disabled ?? false;
		this.#visible = action.visible ?? true;
		this.#visibleWhenReadOnly = action.visibleWhenReadOnly ?? false;
		this.#icon = action.icon ?? "image";
		this.#quick = action.quick ?? false;
		this.#dangerous = action.dangerous ?? false;
		this.title = action.title;
	}
	get visible() {
		if (this.#payload.isReadOnly && !this.#visibleWhenReadOnly) return false;
		return typeof this.#visible === "function" ? this.#visible(this.#payload) : this.#visible;
	}
	get disabled() {
		return typeof this.#disabled === "function" ? this.#disabled(this.#payload) : this.#disabled;
	}
	get quick() {
		return typeof this.#quick === "function" ? this.#quick(this.#payload) : this.#quick;
	}
	get dangerous() {
		return typeof this.#dangerous === "function" ? this.#dangerous(this.#payload) : this.#dangerous;
	}
	get icon() {
		return typeof this.#icon === "function" ? this.#icon(this.#payload) : this.#icon;
	}
	async run() {
		let payload = { ...this.#payload };
		if (this.#confirm) {
			const confirmation = await modal_default(this.#modalProps());
			if (!confirmation.confirmed) return;
			payload = {
				...payload,
				confirmation
			};
		}
		const response = this.#run(payload);
		if (response instanceof Promise) {
			const progress = this.#payload.vm.$progress;
			const name = this.#payload.fieldPathPrefix ?? this.#payload.handle;
			progress.loading(name, true);
			response.finally(() => progress.loading(name, false));
		}
	}
	#modalProps() {
		let props = this.#confirm === true ? {} : { ...this.#confirm };
		if (!props.title) props.title = this.title;
		return props;
	}
};
// resources/js/components/field-actions/toFieldActions.js
function toFieldActions(binding, payload, extraActions = []) {
	return [...Statamic.$fieldActions.get(binding), ...extraActions].map((action) => markRaw(new FieldAction(action, payload))).filter((action) => action.visible);
}
// resources/js/components/field-actions/HasFieldActions.js
var HasFieldActions_default = { computed: {
	fieldActions() {
		return toFieldActions(this.fieldActionBinding, this.fieldActionPayload, this.internalFieldActions);
	},
	internalFieldActions() {
		return [];
	},
	fieldActionPayload() {
		return {};
	},
	fieldActionBinding() {
		return this.config.type + "-fieldtype";
	}
} };
export { toFieldActions as n, FieldAction as r, HasFieldActions_default as t };
