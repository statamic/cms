import Vue from 'vue'

class Stacks {
    constructor(instance) {
        this.stacks = instance.$root.stacks;
    }

    count() {
        return this.stacks.length;
    }

    add(vm) {
        this.stacks.push(vm);
    }

    remove(vm) {
        const i = _.indexOf(this.stacks, vm);
        this.stacks.splice(i, 1);
    }

    stacks() {
        return this.stacks;
    }
}

Object.defineProperties(Vue.prototype, {
    $stacks: {
        get() {
            return new Stacks(this);
        }
    }
});
