export default {

    install(Vue, options) {

        Vue.prototype.translate = function(key, replacements) {
            return translate(key, replacements);
        };

        Vue.prototype.translate_choice = function(key, count, replacements) {
            return translate_choice(key, count, replacements);
        };

    }

};
