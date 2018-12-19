import Vue from 'vue'
const progress = require('nprogress');

class ProgressBar {
    constructor(instance) {
        this.instance = instance;
    }

    loading(loading) {
        loading ? this.start() : this.complete();
    }

    start() {
        progress.start();
    }

    complete() {
        progress.done();
    }
}

Object.defineProperties(Vue.prototype, {
    $progress: {
        get() {
            return new ProgressBar(this);
        }
    }
});
