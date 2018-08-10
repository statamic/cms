export default {

    install(Vue, options) {

        Vue.prototype.resource_url = function(url) {
            return resource_url(url);
        };

    }

};
