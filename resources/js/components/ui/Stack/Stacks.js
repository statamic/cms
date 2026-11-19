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
            vm,
        });
    }

    stacks() {
        return this.$portals.stacks();
    }
}
