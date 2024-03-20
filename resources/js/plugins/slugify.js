export default {
    install(Vue, options) {
        /** @deprecated Use Statamic.$slug API or snake_case global method. */
        Vue.prototype.$slugify = function(text, glue, lang) {
            return Statamic.$slug.separatedBy(glue).in(lang).create(text);
        };
    }
};
