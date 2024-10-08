import Vue from 'vue'

class ToastBus {
    constructor(instance) {
        this.instance = instance;
    }

    success(message, opts) {
        this.instance.$events.$emit('toast.success', message, opts);
    }

    info(message, opts) {
        this.instance.$events.$emit('toast.info', message, opts);
    }

    error(message, opts) {
        this.instance.$events.$emit('toast.error', message, opts);
    }

    intercept() {
        this.instance.$axios.interceptors.response.use(response => {
            const data = response?.data;

            if (!data) return response;

            const promise = data instanceof Blob
                ? data.text().then(text => JSON.parse(text))
                : new Promise(resolve => resolve(data));

            promise.then(json => {
                const toasts = json._toasts ?? [];
                toasts.forEach(toast => this.instance.$toast[toast.type](toast.message, {duration: toast.duration}))
            });

            return response;
        });
    }
}

Object.defineProperties(Vue.prototype, {
    $toast: {
        get() {
            return new ToastBus(this);
        }
    }
});
