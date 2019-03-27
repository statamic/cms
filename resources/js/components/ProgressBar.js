import Vue from 'vue'
const progress = require('nprogress');

const vm = new Vue({

    data: {
        progressing: false,
        names: [],
        timer: null,
    },

    watch: {

        names(names) {
            if (names.length > 0 && !this.progressing) {
                this.start();
            }

            if (names.length === 0 && this.progressing) {
                this.stop();
            }
        }

    },

    methods: {

        start() {
            this.progressing = true;
            this.timer = setTimeout(() => progress.start(), 500);
        },

        stop() {
            if (this.timer) clearTimeout(this.timer);
            progress.done();
            this.progressing = false;
        },

        add(name) {
            if (this.names.indexOf(name) == -1) {
                this.names.push(name);
            }
        },

        remove(name) {
            const i = this.names.indexOf(name);
            this.names.splice(i, 1);
        }

    }

});

class ProgressBar {
    loading(name, loading) {
        loading ? this.start(name) : this.complete(name);
    }
    start(name) {
        vm.add(name);
    }
    complete(name) {
        vm.remove(name);
    }
    names() {
        return vm.names;
    }
    count() {
        return vm.names.length;
    }
    isComplete() {
        return this.count() === 0;
    }
}

Object.defineProperties(Vue.prototype, {
    $progress: {
        get() {
            return new ProgressBar;
        }
    }
});
