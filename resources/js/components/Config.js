export default class Config {
    constructor(store) {
        this.store = store;
    }

    all() {
        return this.store.state.statamic.config;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        this.store.commit('statamic/configValue', { key, value });
    }
}
