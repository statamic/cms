export default {

    install(Vue, options) {

        Vue.prototype.file_icon = function(url) {
            return file_icon(url);
        };

    }

};
