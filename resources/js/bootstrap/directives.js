// Directives
import Vue from 'vue'
import Elastic from '../directives/elastic';

Vue.directive('elastic', Elastic);

Vue.directive('focus', {
    inserted: function (el) {
        el.focus();
    }
})
