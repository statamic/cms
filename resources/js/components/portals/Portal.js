import uniqid from 'uniqid'
import { useStore } from 'vuex';

export default class Portal {

    constructor(name, data = {}) {
        this.id = `${name}-${uniqid()}`;
        this.data = data;
    }

    destroy() {
        const store = useStore()

        store.dispatch('portals/destroy', this.id)
    }
}
