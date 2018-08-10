// Directives
import Vue from 'vue'
import Tip from '../directives/tip';
import Elastic from '../directives/elastic';

Vue.directive('elastic', Elastic);
Vue.directive('tip', Tip);

Vue.directive('focus', function (focusable) {
    if (! focusable) {
        return;
    }

    if ($('[autofocus]').length > 0 && ! $(this.el).within('.form-group').length) {
      return;
    }

    this.vm.$nextTick(() => this.el.focus());
})
