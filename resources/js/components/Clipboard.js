import Vue from 'vue'
import Cookies from 'cookies-js';
import uid from 'uniqid';

const vm = new Vue({

    data: {
        id: null,
        data: null,
    },

    created() {
        let id = Cookies.get('statamic.clipboard');
        if (!id) {
            id = uid();
            Cookies.set('statamic.clipboard', id);
        }
        this.id = id;
        this.data = this.storageParse(this.storageRead());
        window.addEventListener('storage', (event) => {
            if (event.key === 'statamic.clipboard') {
                this.data = this.storageParse(event.newValue);
            }
        });
    },

    methods: {

        set(data) {
            this.data = data;
            this.storageWrite(this.data);
        },
        
        get() {
            return this.data;
        },
        
        clear() {
            this.data = null;
            this.storageClear();
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

        storageClear() {
            localStorage.removeItem('statamic.clipboard');
        },

        storageParse(value) {
            const parsed = JSON.parse(value);
            if (!parsed) {
                return null;
            }
            const { id, payload } = parsed;
            if (id !== this.id) {
                this.storageClear();
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
    clear(data) {
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
