
export default class Stacks {
    constructor(instance) {
        // @todo(jelleroorda): what's this
        this.portals = [] // instance.$root.portals;
    }

    count() {
        return this.stacks().length;
    }

    add(vm) {
        console.log(vm, this.portals);

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
