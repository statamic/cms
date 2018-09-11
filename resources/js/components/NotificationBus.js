import Vue from 'vue'

class NotificationBus {
    constructor(instance) {
        this.instance = instance;
    }

    success(message, opts) {
        this.instance.$events.$emit('notify.success', message, opts);
    }

    error(message, opts) {
        this.instance.$events.$emit('notify.error', message, opts);
    }
}

Object.defineProperties(Vue.prototype, {
    $notify: {
        get() {
            return new NotificationBus(this);
        }
    }
});
