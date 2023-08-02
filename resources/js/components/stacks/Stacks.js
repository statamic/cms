import Vue from 'vue'
import uniqid from 'uniqid'

class Stacks {
    constructor(instance) {
        this.portals = instance.$root.portals;
    }

    count() {
        return this.stacks().length;
    }

    add(vm) {
        return vm.$portals.create('stack', {
            type: 'stack',
            depth: this.count() + 1,
            vm
        });
    }

    stacks() {
        return this.portals.filter(portal => portal.data?.type === 'stack');
    }
}

Object.defineProperties(Vue.prototype, {
    $stacks: {
        get() {
            return new Stacks(this);
        }
    }
});
