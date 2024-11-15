import modal from './modal';

export default class FieldAction {
    #payload;
    #run;
    #visible;
    #icon;
    #quick;

    constructor(action, payload) {
        this.#payload = payload;
        this.#run = action.run;
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
        this.#run({...this.#payload, modal});
    }
}
