export default class Permission {
    all() {
        return Statamic.user?.permissions || [];
    }

    has(permission) {
        return this.all().includes(permission) || this.all().includes('super');
    }
}
