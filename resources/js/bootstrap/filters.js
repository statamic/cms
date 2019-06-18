import Vue from 'vue';

Vue.filter('caseInsensitiveOrderBy', require('../filters/orderby'));
Vue.filter('deslugify', require('../filters/deslugify'));
Vue.filter('markdown', require('../filters/markdown'));
Vue.filter('parse', require('../filters/parse'));
Vue.filter('pre', require('../filters/pre'));
Vue.filter('pluck', require('../filters/pluck'));
Vue.filter('reverse', require('../filters/reverse'));
Vue.filter('striptags', require('../filters/striptags'));
Vue.filter('titleize', require('../filters/titleize'));
