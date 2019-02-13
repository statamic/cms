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
        return this.all()[key] || fallback;
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
}

Object.defineProperties(Vue.prototype, {
    $preferences: {
        get() {
            return new Preference(this);
        }
    }
});
