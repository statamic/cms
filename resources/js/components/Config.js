import Vue from 'vue';

class Config {
    constructor(instance) {
        this.instance = instance;
    }

    all() {
        return this.instance.$store.state.statamic.config;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        this.instance.$store.commit('statamic/configValue', {key, value});
    }
}

Object.defineProperties(Vue.prototype, {
    $config: {
        get() {
            return new Config(this);
        }
    }
});
