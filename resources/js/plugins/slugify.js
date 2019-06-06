var getSlug = require('speakingurl');

export default {
    install(Vue, options) {
        Vue.prototype.$slugify = function(text, glue) {
            return getSlug(text, {
                separator: glue || '-'
            });
        };
    }
};
