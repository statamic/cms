export default class Stacks {
    constructor(portals) {
        this.$portals = portals;
    }

    count() {
        return this.stacks().length;
    }

    add(vm) {
        return this.$portals.create('stack', {
            type: 'stack',
            depth: this.count() + 1,
            vm
        });
    }

    stacks() {
        return this.$portals.stacks();
    }
}
