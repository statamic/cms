import modal from './modal';

export default class FieldAction {
    #payload;
    #run;
    #visible;
    #icon;
    #quick;
    #confirm;

    constructor(action, payload) {
        this.#payload = payload;
        this.#run = action.run;
        this.#confirm = action.confirm === true ? {} : action.confirm;
        this.#visible = action.visible ?? true;
        this.#icon = action.icon ?? 'image';
        this.#quick = action.quick ?? false;
        this.title = action.title;
    }

    get visible() {
        return typeof this.#visible === 'function' ? this.#visible(this.#payload) : this.#visible;
    }

    get quick() {
        return typeof this.#quick === 'function' ? this.#quick(this.#payload) : this.#quick;
    }

    get icon() {
        return typeof this.#icon === 'function' ? this.#icon(this.#payload) : this.#icon;
    }

    async run() {
        let payload = {...this.#payload};

        if (this.#confirm) {
            const confirmation = await modal(this.#confirm);
            if (!confirmation.confirmed) return;
            payload = {...payload, confirmation};
        }

        const response = this.#run(payload);

        if (response instanceof Promise) {
            const progress = this.#payload.vm.$progress;
            const name = this.#payload.fieldPathPrefix ?? this.#payload.handle;
            progress.loading(name, true);
            response.finally(() => progress.loading(name, false));
        }
    }
}
