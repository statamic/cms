import Vue from 'vue';

class Preference {
    constructor(instance) {
        this.instance = instance;
    }

    all() {
        return this.instance.$store.state.statamic.preferences;
    }

    get(key, fallback) {
        return this.all()[key] || fallback;
    }
}

Object.defineProperties(Vue.prototype, {
    $preferences: {
        get() {
            return new Preference(this);
        }
    }
});
