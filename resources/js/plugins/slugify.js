import { slugify } from 'transliterations';

export default {

    install(Vue, options) {

        Vue.prototype.$slugify = function(text, glue) {
            return slugify(text, {
                separator: glue || '-'
            });
        };

    }

};
