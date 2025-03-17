import { ref } from 'vue';

export default class Config {
    constructor(initialConfig) {
        this.config = ref(initialConfig);
    }

    all() {
        return this.config.value;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        this.config.value[key] = value;
    }
}
