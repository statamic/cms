import Vue from 'vue';


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

import './components/portals/Portals';
import './components/stacks/Stacks';
import './components/Permission';
