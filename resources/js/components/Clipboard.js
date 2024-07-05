import Vue from 'vue'

const vm = new Vue({

    data: {
        data: null,
    },

    created() {
        window.addEventListener('storage', (event) => this.sync(event));
        this.set(JSON.parse(localStorage.getItem('statamic.clipboard')));
    },

    methods: {

        sync(event) {
            if (event.key !== 'statamic.clipboard') {
                return;
            }
            const data = JSON.parse(event.newValue);
            if (!data) {
                return;
            }
            this.set(data);
        },
        
        set(data) {
            this.data = data;
            localStorage.setItem('statamic.clipboard', JSON.stringify(this.data));
        },
    
        clear() {
            this.data = null;
            localStorage.removeItem('statamic.clipboard');
        },

    }

});

class Clipboard {
    set(data) {
        vm.set(data);
    }
    get() {
        return vm.data;
    }
    clear() {
        vm.clear();
    }
}

Object.defineProperties(Vue.prototype, {
    $clipboard: {
        get() {
            return new Clipboard;
        }
    }
});
