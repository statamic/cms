import FieldConditions from './components/FieldConditions.js';

// TODO: How to handle dependency?
window._ = require('underscore');

// TODO: How to handle dependency?
window.data_get = function(obj, path, fallback=null) {
    // Source: https://stackoverflow.com/a/22129960
    var properties = Array.isArray(path) ? path : path.split('.');
    var value = properties.reduce((prev, curr) => prev && prev[curr], obj);
    return value !== undefined ? value : fallback;
};

class Statamic {
    constructor() {
        this.$conditions = new FieldConditions;
    }
}

window.Statamic = new Statamic;
