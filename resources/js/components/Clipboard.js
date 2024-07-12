import Vue from 'vue'
import Cookies from 'cookies-js';

const vm = new Vue({

    data: {
        data: null,
    },

    created() {
        this.data = this.storageParse(this.storageRead());
        window.addEventListener('storage', (event) => {
            if (event.key === 'statamic.clipboard') {
                this.data = this.storageParse(event.newValue);
            }
        });
    },

    computed: {

        id() {
            let id = Cookies.get('statamic.clipboard');
            if (!id) {
                id = uniqid();
                Cookies.set('statamic.clipboard', id);
            }
            return id;
        },

    },

    methods: {
        
        set(data) {
            this.data = data;
            this.storageWrite(this.data);
        },
        
        get() {
            return this.data;
        },
    
        storageRead() {
            return localStorage.getItem('statamic.clipboard');
        },

        storageWrite(data) {
            localStorage.setItem('statamic.clipboard', JSON.stringify({
                id: this.id,
                payload: data,
            }));
        },

        storageParse(value) {
            const parsed = JSON.parse(value);
            if (!parsed) {
                return null;
            }
            const { id, payload } = parsed;
            if (id !== this.id) {
                localStorage.removeItem('statamic.clipboard');
                return null;
            }
            return payload;
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
}

Object.defineProperties(Vue.prototype, {
    $clipboard: {
        get() {
            return new Clipboard;
        }
    }
});
