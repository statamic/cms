import Vue from 'vue';
import Toast from './mixins/Toast.js';
import Statamic from './components/Statamic.js';
import Alpine from 'alpinejs'
import * as Globals from './bootstrap/globals'
import { default as underscore } from 'underscore'

let global_functions = Object.keys(Globals)
global_functions.forEach(fnName => { global[fnName] = Globals[fnName] })
global.Cookies = require('cookies-js');

Vue.config.silent = false;
Vue.config.devtools = true;
Vue.config.productionTip = false

window.Alpine = Alpine
window.Vue = Vue;
window.Statamic = Statamic;
window._ = underscore;
window.$ = window.jQuery = require('jquery');
window.rangy = require('rangy');

require('./bootstrap/polyfills');
require('./bootstrap/underscore-mixins');
require('./bootstrap/jquery-plugins');
require('./bootstrap/plugins');
require('./bootstrap/filters');
require('./bootstrap/mixins');
require('./bootstrap/components');
require('./bootstrap/fieldtypes');
require('./bootstrap/directives');

import axios from 'axios';
import PortalVue from "portal-vue";
import VModal from "vue-js-modal";
import Vuex from 'vuex';
import StatamicStore from './store';
import Popover  from 'vue-js-popover'
import VTooltip from 'v-tooltip'
import ReactiveProvide from 'vue-reactive-provide';
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

Vue.prototype.$axios = axios;
Vue.prototype.$events = new Vue();
Vue.prototype.$echo = Statamic.$echo;
Vue.prototype.$bard = Statamic.$bard;
Vue.prototype.$keys = Statamic.$keys;

window.moment = Vue.moment = Vue.prototype.$moment = require('moment');

Vue.use(Popover, { tooltip: true })
Vue.use(PortalVue)
Vue.use(VModal, { componentName: 'vue-modal' })
Vue.use(VTooltip)
Vue.use(Vuex);
Vue.use(ReactiveProvide);
Vue.use(VCalendar);

Vue.component(vSelect)

Statamic.$store = new Vuex.Store({
    modules: {
        statamic: StatamicStore,
        publish: {
            namespaced: true
        }
    }
});

require('./components/ToastBus');
require('./components/ModalBus');
require('./components/stacks/Stacks');
require('./components/panes/Panes');
require('./components/ProgressBar');
require('./components/DirtyState');
require('./components/Config');
require('./components/Preference');
require('./components/Permission');

Statamic.app({
    el: '#statamic',

    mixins: [Toast],

    store: Statamic.$store,

    components: {
        GlobalSearch: require('./components/GlobalSearch.vue').default,
        GlobalSiteSelector: require('./components/GlobalSiteSelector.vue').default,
        Login: require('./components/login/login'),
        LoginModal: require('./components/login/LoginModal.vue').default,
        BaseEntryCreateForm: require('./components/entries/BaseCreateForm.vue').default,
        BaseTermCreateForm: require('./components/terms/BaseCreateForm.vue').default,
        CreateTermButton: require('./components/terms/CreateTermButton.vue').default,
        Importer: require('./components/importer/importer'),
        FieldsetListing: require('./components/fieldsets/Listing.vue').default,
        FieldsetCreateForm: require('./components/fieldsets/CreateForm.vue').default,
        FieldsetEditForm: require('./components/fieldsets/EditForm.vue').default,
        BlueprintListing: require('./components/blueprints/Listing.vue').default,
        BlueprintBuilder: require('./components/blueprints/Builder.vue').default,
        FormCreateForm: require('./components/forms/CreateForm.vue').default,
        FormListing: require('./components/forms/Listing.vue').default,
        FormSubmissionListing: require('./components/forms/SubmissionListing.vue').default,
        GlobalListing: require('./components/globals/Listing.vue').default,
        GlobalEditForm: require('./components/globals/EditForm.vue').default,
        GlobalPublishForm: require('./components/globals/PublishForm.vue').default,
        GlobalCreateForm: require('./components/globals/Create.vue').default,
        UserListing: require('./components/users/Listing.vue').default,
        UserWizard: require('./components/users/Wizard.vue').default,
        RoleListing: require('./components/roles/Listing.vue').default,
        RolePublishForm: require('./components/roles/PublishForm.vue').default,
        UserGroupListing: require('./components/user-groups/Listing.vue').default,
        UserGroupPublishForm: require('./components/user-groups/PublishForm.vue').default,
        CollectionCreateForm: require('./components/collections/CreateForm.vue').default,
        CollectionScaffolder: require('./components/collections/Scaffolder.vue').default,
        CollectionEditForm: require('./components/collections/EditForm.vue').default,
        CollectionView: require('./components/collections/View.vue').default,
        CollectionBlueprintListing:  require('./components/collections/BlueprintListing.vue').default,
        SessionExpiry: require('./components/SessionExpiry.vue').default,
        NavigationListing: require('./components/navigation/Listing.vue').default,
        NavigationCreateForm: require('./components/navigation/CreateForm.vue').default,
        NavigationEditForm: require('./components/navigation/EditForm.vue').default,
        PreferencesEditForm: require('./components/preferences/EditForm.vue').default,
        NavigationView: require('./components/navigation/View.vue').default,
        TaxonomyCreateForm: require('./components/taxonomies/CreateForm.vue').default,
        TaxonomyEditForm: require('./components/taxonomies/EditForm.vue').default,
        TaxonomyBlueprintListing:  require('./components/taxonomies/BlueprintListing.vue').default,
        AssetContainerCreateForm: require('./components/asset-containers/CreateForm.vue').default,
        AssetContainerEditForm: require('./components/asset-containers/EditForm.vue').default,
        NavBuilder: require('./components/nav/Builder.vue').default,
        Updater: require('./components/updater/Updater.vue').default,
        PortalTargets: require('./components/PortalTargets.vue').default,
    },

    data: {
        showLoginModal: false,
        navOpen: true,
        mobileNavOpen: false,
        showBanner: true,
        portals: [],
        panes: [],
        appendedComponents: []
    },

    computed: {

        version() {
            return Statamic.$config.get('version');
        },

        stackCount() {
            return this.$stacks.count();
        },

        wrapperClass() {
            return this.$config.get('wrapperClass', 'max-w-xl');
        }

    },

    mounted() {
        this.bindWindowResizeListener();

        this.$keys.bind(['command+\\'], e => {
            e.preventDefault();
            this.toggleNav();
        });

        if (this.$config.get('broadcasting.enabled')) {
            this.$echo.start();
        }

        // Set moment locale
        window.moment.locale(Statamic.$config.get('locale'));
        Vue.moment.locale(Statamic.$config.get('locale'));
        Vue.prototype.$moment.locale(Statamic.$config.get('locale'));

        this.fixAutofocus();

        this.showBanner = Statamic.$config.get('hasLicenseBanner');

        this.$toast.intercept();
    },

    created() {
        const state = localStorage.getItem('statamic.nav') || 'open';
        this.navOpen = state === 'open';

        Statamic.$callbacks.add('copyToClipboard', async function (url) {
            try {
                await navigator.clipboard.writeText(url);
                Statamic.$toast.success(__('Copied to clipboard'));
            } catch (err) {
                await alert(url);
            }
        });

        Statamic.$callbacks.add('bustAndReloadImageCaches', function (urls) {
            urls.forEach(async url => {
                await fetch(url, { cache: 'reload', mode: 'no-cors' });
                document.body
                    .querySelectorAll(`img[src='${url}']`)
                    .forEach(img => img.src = url);
            });
        });
    },

    methods: {

        bindWindowResizeListener() {
            window.addEventListener('resize', () => {
                this.$store.commit('statamic/windowWidth', document.documentElement.clientWidth);
            });
            window.dispatchEvent(new Event('resize'));
        },

        toggleNav() {
            this.navOpen = ! this.navOpen;
            localStorage.setItem('statamic.nav', this.navOpen ? 'open' : 'closed');
        },

        toggleMobileNav() {
            this.mobileNavOpen = ! this.mobileNavOpen;
        },

        hideBanner() {
            this.showBanner = false;
        },

        fixAutofocus() {
            // Fix autofocus issues in Safari and Firefox
            setTimeout(() => {
                const inputs = document.querySelectorAll('input[autofocus]');
                for (let input of inputs) {
                    input.blur();
                }
                if (inputs.length) {
                    inputs[0].focus();
                }
            }, 100);
        }
    }

});
