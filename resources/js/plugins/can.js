const can = function(permission) {
    const permissions = JSON.parse(atob(Statamic.$config.get('permissions')));

    return permissions.includes('super') || permissions.includes(permission);
};

export default {

    install(Vue, options) {

        Vue.prototype.can = function(permission) {
            return can(permission);
        };

    }

};
