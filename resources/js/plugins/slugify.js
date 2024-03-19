import axios from 'axios';

export default {
    install(Vue, options) {
        Vue.prototype.$slugify = function(string, separator, lang) {
            const selectedSite = Statamic.$config.get('selectedSite');
            const sites = Statamic.$config.get('sites');
            const site = sites.find(site => site.handle === selectedSite);
            lang = lang ?? site?.lang ?? Statamic.$config.get('lang');

            return new Promise((resolve, reject) => {
                axios.post(cp_url('slug'), { string, separator, language: lang })
                    .then(response => resolve(response.data))
                    .catch(error => reject(error));
            });
        };
    }
};
