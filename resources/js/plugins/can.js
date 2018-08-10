if (Statamic.permissions) {
    var permissions = JSON.parse(atob(Statamic.permissions));
}

var can = function(permission) {
    if (_.contains(permissions, 'super')) {
        return true;
    }

    var colons = permission.split(':').length - 1;

    if (colons === 2) {
        var parts = permission.split(':');
        var cascade = parts[0] + ':';
        if (parts[2] === 'delete') {
            cascade += 'delete';
        } else {
            cascade += 'manage';
        }

        if (_.contains(permissions, cascade)) {
            return true;
        }
    }

    return _.contains(permissions, permission);
};

export default {

    install(Vue, options) {

        Vue.prototype.can = function(permission) {
            return can(permission);
        };

        Vue.can = function(permission) {
            return can(permission);
        };

    }

};
