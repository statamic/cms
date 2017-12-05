exports.install = function(Vue, options) {

    Vue.prototype.$slugify = function(text, glue) {
        return slug(text, {
            replacement: glue || '-',
            lower: true
        });
    };

};
