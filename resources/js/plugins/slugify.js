var getSlug = require('speakingurl');
const langCharMap = {
    'da': { // Danish
        'æ': 'ae',
        'Æ': 'Ae',
        'ø': 'oe',
        'Ø': 'Oe',
        'å': 'aa',
        'Å': 'Aa',
    },
};

export default {
    install(Vue, options) {
        Vue.prototype.$slugify = function(text, glue) {
            const selectedSite = Statamic.$config.get('selectedSite');
            const sites = Statamic.$config.get('sites');
            const site = sites.find(site => site.handle === selectedSite);
            const lang = site?.locale ?? Statamic.$config.get('locale');
            const custom = langCharMap[lang] ?? undefined;

            return getSlug(text, { separator: glue || '-', lang, custom });
        };
    }
};
