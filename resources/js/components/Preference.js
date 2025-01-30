import axios from 'axios';

export default class Preference {
    constructor(store) {
        this.store = store;
        this.url = cp_url('preferences/js');
    }

    all() {
        return this.store.state.statamic.config.user.preferences;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        return this.commitOnSuccessAndReturnPromise(
            axios.post(this.url, {key, value})
        );
    }

    append(key, value) {
        return this.commitOnSuccessAndReturnPromise(
            axios.post(this.url, {key, value, append: true})
        );
    }

    remove(key, value=null, cleanup=true) {
        return this.commitOnSuccessAndReturnPromise(
            axios.delete(`${this.url}/${key}`, { data: { value, cleanup } })
        );
    }

    removeValue(key, value) {
        return this.remove(key, value);
    }

    commitOnSuccessAndReturnPromise(promise) {
        promise.then(response => {
            this.store.commit('statamic/preferences', response.data);
        });

        return promise;
    }

    defaults() {
        return Statamic.$config.get('defaultPreferences');
    }

    getDefault(key, fallback) {
        return data_get(this.defaults(), key, fallback);
    }

    hasDefault(key) {
        return this.getDefault(key) !== null;
    }
}
