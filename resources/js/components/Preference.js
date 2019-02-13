import Vue from 'vue';

class Preference {
    constructor(instance) {
        this.instance = instance;
        this.storeUrl = '/cp/preferences';
    }

    all() {
        return this.instance.$store.state.statamic.preferences;
    }

    get(key, fallback) {
        return this.dotNotationGet(key, this.all()) || fallback;
    }

    set(key, value) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.axios.post(this.storeUrl, {'key': key, 'value': value})
        );
    }

    append(key, value) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.axios.post(this.storeUrl, {'key': key, 'value': value, append: true})
        );
    }

    remove(key) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.axios.delete(`${this.storeUrl}/${key}`)
        );
    }

    commitOnSuccessAndReturnPromise(promise) {
        promise.then(response => {
            this.instance.$store.commit('statamic/preferences', response.data);
        });

        return promise;
    }

    // Because we don't have access to lodash, and underscore doesn't have a direct solution.
    // Source: https://stackoverflow.com/a/22129960
    dotNotationGet(path, obj) {
        var properties = Array.isArray(path) ? path : path.split('.');
        return properties.reduce((prev, curr) => prev && prev[curr], obj);
    }
}

Object.defineProperties(Vue.prototype, {
    $preferences: {
        get() {
            return new Preference(this);
        }
    }
});
