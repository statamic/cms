// Directives
import Vue from 'vue'
import Tip from '../directives/tip';
import Elastic from '../directives/elastic';

Vue.directive('elastic', Elastic);
Vue.directive('tip', Tip);

Vue.directive('focus', {
    inserted: function (el) {
        el.focus();
    }
})
