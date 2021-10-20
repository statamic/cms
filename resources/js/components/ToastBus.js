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
            const toasts = response?.data?._toasts ?? []

            toasts.forEach(toast => this.instance.$toast[toast.type](toast.message, {duration: toast.duration}))

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
