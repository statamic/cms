import { ref } from 'vue';
import { data_get } from '@/bootstrap/globals';

export default class Config {
    config = ref({});

    initialize(initialConfig) {
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
