export default {
    install(Vue, options) {
        /** @deprecated Use Statamic.$slug API, str_snake, or snake_case global methods. */
        Vue.prototype.$slugify = function(text, glue, lang) {
            return Statamic.$slug.separatedBy(glue).in(lang).create(text);
        };
    }
};
