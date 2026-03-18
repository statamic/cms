import axios from 'axios';
import { ref } from 'vue';

export default class Preference {
    #url;
    #preferences;
    #defaults;

    initialize(preferences, defaults) {
        this.#url = cp_url('preferences/js');
        this.#preferences = ref(preferences);
        this.#defaults = defaults;
    }

    all() {
        return this.#preferences.value;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        return this.commitOnSuccessAndReturnPromise(axios.post(this.#url, { key, value }));
    }

    append(key, value) {
        return this.commitOnSuccessAndReturnPromise(axios.post(this.#url, { key, value, append: true }));
    }

    has(key) {
        return this.all().hasOwnProperty(key);
    }

    remove(key, value = null, cleanup = true) {
        return this.commitOnSuccessAndReturnPromise(axios.delete(`${this.#url}/${key}`, { data: { value, cleanup } }));
    }

    removeValue(key, value) {
        return this.remove(key, value);
    }

    commitOnSuccessAndReturnPromise(promise) {
        promise.then((response) => {
            this.#preferences.value = response.data;
        });

        return promise;
    }

    defaults() {
        return this.#defaults;
    }

    getDefault(key, fallback) {
        return data_get(this.defaults(), key, fallback);
    }

    hasDefault(key) {
        return this.getDefault(key) !== null;
    }
}
