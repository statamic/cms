var getSlug = require('speakingurl');

export default {
    install(Vue, options) {
        Vue.prototype.$slugify = function(text, glue, lang) {
            const selectedSite = Statamic.$config.get('selectedSite');
            const sites = Statamic.$config.get('sites');
            const site = sites.find(site => site.handle === selectedSite);
            lang = lang ?? site?.lang ?? Statamic.$config.get('lang');
            const custom = Statamic.$config.get(`charmap.${lang}`) ?? undefined;

            return getSlug(text, {
                separator: glue || '-',
                lang,
                custom,
                symbols: Statamic.$config.get('asciiReplaceExtraSymbols')
            });
        };
    }
};
