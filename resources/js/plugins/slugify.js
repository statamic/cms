import axios from 'axios';

export default {
    install(Vue, options) {
        Vue.prototype.$slugify = function(text, glue, lang) {
            const selectedSite = Statamic.$config.get('selectedSite');
            const sites = Statamic.$config.get('sites');
            const site = sites.find(site => site.handle === selectedSite);
            lang = lang ?? site?.lang ?? Statamic.$config.get('lang');

            return new Promise((resolve, reject) => {
                axios.post(cp_url('slug'), { text, glue, language: lang })
                    .then(response => resolve(response.data.slug))
                    .catch(error => reject(error));
            });
        };
    }
};
