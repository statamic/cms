import Vue from 'vue';

class Preference {
    constructor(instance) {
        this.instance = instance;
        this.storeUrl = cp_url('preferences');
    }

    all() {
        return this.instance.$store.state.statamic.config.user.preferences;
    }

    get(key, fallback) {
        return data_get(this.all(), key, fallback);
    }

    set(key, value) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.$axios.post(this.storeUrl, {key, value})
        );
    }

    append(key, value) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.$axios.post(this.storeUrl, {key, value, append: true})
        );
    }

    remove(key, value=null, cleanup=true) {
        return this.commitOnSuccessAndReturnPromise(
            this.instance.$axios.post(`${this.storeUrl}/${key}/delete`, {value, cleanup})
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
