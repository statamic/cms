export default class Modals {
    constructor(portals) {
        this.$portals = portals;
    }

    count() {
        return this.modals().length;
    }

    add(vm) {
        return this.$portals.create('modal', {
            type: 'modal',
            vm,
        });
    }

    modals() {
        return this.$portals.all().filter((portal) => portal.data?.type === 'modal');
    }
}
