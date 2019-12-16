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
}

Object.defineProperties(Vue.prototype, {
    $toast: {
        get() {
            return new ToastBus(this);
        }
    }
});
