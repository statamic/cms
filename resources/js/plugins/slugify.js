export default {
    install(Vue, options) {
        Vue.prototype.$slugify = function(text, glue, lang) {
            return Statamic.$slug.separatedBy(glue).in(lang).create(text);
        };
    }
};
