import Vue from 'vue'

class NotificationBus {
    constructor(instance) {
        this.instance = instance;
    }

    success(message, opts) {
        this.instance.$dispatch('setFlashSuccess', message, opts);
    }

    error(message, opts) {
        this.instance.$dispatch('setFlashError', message, opts);
    }
}

Object.defineProperties(Vue.prototype, {
    $notify: {
        get() {
            return new NotificationBus(this);
        }
    }
});
