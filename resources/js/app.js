import Moment from 'moment';
window.moment = Moment;

// Assign the global functions from the bootstrap/globals.js file to the window
import * as Globals from './bootstrap/globals'
Object.assign(window, Globals);

import Statamic from './Statamic.js';
window.Statamic = Statamic;

// import Alpine from 'alpinejs';
// Alpine.start()
//
// import { default as underscore } from 'underscore'
// import Cookies from 'cookies-js';
//
// import.meta.glob(['../img/**']);
//
// window.Cookies = Cookies;
// window.Alpine = Alpine;



// import './bootstrap/polyfills';
// import './bootstrap/underscore-mixins';
// // import './bootstrap/plugins';
// import './bootstrap/filters';
// import './bootstrap/fieldtypes';
// import './bootstrap/directives';
// import './bootstrap/tooltips';
// import './bootstrap/mixins';

// import PortalVue from "portal-vue";
// import VModal from "vue-js-modal";
// import vSelect from 'vue-select'
// import VCalendar from 'v-calendar';
//
// // Customize vSelect UI components @todo(jelleroorda): fix this.
// vSelect.props.components.default = () => ({
//     Deselect: {
//         render: createElement => createElement('span', __('×')),
//     },
//     OpenIndicator: {
//         render: createElement => createElement('span', {
//             class: { 'toggle': true },
//             domProps: {
//                 innerHTML: '<svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 20 20"><path fill="currentColor" d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>'
//             }
//         })
//     }
// });
//


Statamic.booting(Statamic => {
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = Statamic.$config.get('csrfToken');
});

// import './components/ToastBus';
// import './components/portals/Portals';
// import './components/stacks/Stacks';
// import './components/ProgressBar';
// import './components/DirtyState';
// import './components/Config';
// import './components/Preference';
// import './components/Permission';
