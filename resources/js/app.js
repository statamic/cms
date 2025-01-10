import Vue from 'vue';
import Toast from './mixins/Toast.js';


window.Vue = Vue;

import './bootstrap/polyfills';
import './bootstrap/underscore-mixins';
import './bootstrap/plugins';
import './bootstrap/mixins';
import './bootstrap/components';
import './bootstrap/fieldtypes';
import './bootstrap/directives';

import axios from 'axios';
import PortalVue from "portal-vue";
import VModal from "vue-js-modal";
import vSelect from 'vue-select'
import VCalendar from 'v-calendar';

// Customize vSelect UI components
vSelect.props.components.default = () => ({
    Deselect: {
        render: createElement => createElement('span', __('×')),
    },
    OpenIndicator: {
        render: createElement => createElement('span', {
            class: { 'toggle': true },
            domProps: {
                innerHTML: '<svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 20 20"><path fill="currentColor" d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>'
            }
        })
    }
});

Statamic.booting(Statamic => {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['X-CSRF-TOKEN'] = Statamic.$config.get('csrfToken');
});

Alpine.start()

Vue.prototype.$echo = Statamic.$echo;
Vue.prototype.$bard = Statamic.$bard;
Vue.prototype.$keys = Statamic.$keys;
Vue.prototype.$reveal = Statamic.$reveal;
Vue.prototype.$fieldActions = Statamic.$fieldActions;
Vue.prototype.$slug = Statamic.$slug;


Vue.use(PortalVue, { portalName: 'v-portal' })
Vue.use(VModal, { componentName: 'v-modal' })
Vue.use(VCalendar);

Vue.component(vSelect)

import './components/ToastBus';
import './components/portals/Portals';
import './components/stacks/Stacks';
import './components/Permission';
