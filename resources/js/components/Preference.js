import Vue from 'vue';

class Preference {
    constructor(instance) {
        this.instance = instance;
        this.storeUrl = '/cp/preferences';
    }

    all() {
        return this.instance.$store.state.statamic.config.preferences;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
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

    remove(key, value=null) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.axios.delete(`${this.storeUrl}/${key}`, {data: {'value': value}})
        );
    }

    removeValue(key, value) {
        return this.remove(key, value);
    }

    commitOnSuccessAndReturnPromise(promise) {
        promise.then(response => {
            this.instance.$store.commit('statamic/preferences', response.data);
        });

        return promise;
    }
}

Object.defineProperties(Vue.prototype, {
    $preferences: {
        get() {
            return new Preference(this);
        }
    }
});
