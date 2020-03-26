export default {

    install(Vue, options) {

        Vue.prototype.docs_url = function(url) {
            return docs_url(url);
        };

    }

};
