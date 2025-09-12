import axios from 'axios';
import { ref } from 'vue';

export default class Preference {
    constructor() {
        this.url = cp_url('preferences/js');
        this.preferences = ref(Statamic.$config.get('user.preferences'));
    }

    all() {
        return this.preferences.value;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        return this.commitOnSuccessAndReturnPromise(axios.post(this.url, { key, value }));
    }

    append(key, value) {
        return this.commitOnSuccessAndReturnPromise(axios.post(this.url, { key, value, append: true }));
    }

    has(key) {
        return this.all().hasOwnProperty(key);
    }

    remove(key, value = null, cleanup = true) {
        return this.commitOnSuccessAndReturnPromise(axios.delete(`${this.url}/${key}`, { data: { value, cleanup } }));
    }

    removeValue(key, value) {
        return this.remove(key, value);
    }

    commitOnSuccessAndReturnPromise(promise) {
        promise.then((response) => {
            this.preferences.value = response.data;
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
