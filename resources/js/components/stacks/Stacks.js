import Vue from 'vue'

class Stacks {
    constructor(instance) {
        this.instance = instance;
        this.stacks = instance.$root.stacks;
    }

    count() {
        return this.stacks.length;
    }

    add(name) {
        this.stacks.push(name);
    }

    remove(name) {
        const i = this.stacks.indexOf(name);
        this.stacks.splice(i, 1);
    }
}

Object.defineProperties(Vue.prototype, {
    $stacks: {
        get() {
            return new Stacks(this);
        }
    }
});
