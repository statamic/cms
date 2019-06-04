import Vue from 'vue';

class Permission {
    constructor(instance) {
        this.instance = instance;
    }

    all() {
        return this.instance.$store.state.statamic.config.user.permissions;
    }

    has(permission) {
        return this.all().includes(permission);
    }
}

Object.defineProperties(Vue.prototype, {
    $permissions: {
        get() {
            return new Permission(this);
        }
    }
});
