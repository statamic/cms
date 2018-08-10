import Vue from 'vue';

Vue.filter('deslugify', require('../filters/deslugify'));
Vue.filter('titleize', require('../filters/titleize'));
Vue.filter('pre', require('../filters/pre'));
Vue.filter('reverse', require('../filters/reverse'));
Vue.filter('pluck', require('../filters/pluck'));
Vue.filter('parse', require('../filters/parse'));
Vue.filter('optionize', require('../filters/optionize'));
Vue.filter('markdown', require('../filters/markdown'));
Vue.filter('caseInsensitiveOrderBy', require('../filters/orderby'));
