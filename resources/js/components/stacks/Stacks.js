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
        const portal = {
            type: 'stack',
            key: uniqid(),
            depth: this.count() + 1,
            vm
        };

        this.portals.push(portal);

        return portal;
    }

    remove(vm) {
        const i = _.findIndex(this.portals, (item) => item.vm === vm);
        this.portals.splice(i, 1);
    }

    stacks() {
        return this.portals.filter(portal => portal.type === 'stack');
    }
}

Object.defineProperties(Vue.prototype, {
    $stacks: {
        get() {
            return new Stacks(this);
        }
    }
});
