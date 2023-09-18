import Vue from 'vue';
import Toast from './mixins/Toast.js';
import Statamic from './components/Statamic.js';
import Alpine from 'alpinejs'
import * as Globals from './bootstrap/globals'
import { default as underscore } from 'underscore'
import Cookies from 'cookies-js';

import.meta.glob(['../img/**']);

let global_functions = Object.keys(Globals)
global_functions.forEach(fnName => { window[fnName] = Globals[fnName] })

Vue.config.silent = false;
Vue.config.devtools = true;
Vue.config.productionTip = false

window.Cookies = Cookies;
window.Alpine = Alpine
window.Vue = Vue;
window.Statamic = Statamic;
window._ = underscore;

import './bootstrap/polyfills';
import './bootstrap/underscore-mixins';
import './bootstrap/plugins';
import './bootstrap/filters';
import './bootstrap/mixins';
import './bootstrap/components';
import './bootstrap/fieldtypes';
import './bootstrap/directives';
import './bootstrap/tooltips';

import axios from 'axios';
import PortalVue from "portal-vue";
import VModal from "vue-js-modal";
import Vuex from 'vuex';
import StatamicStore from './store';
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

import Moment from 'moment';
window.moment = Vue.moment = Vue.prototype.$moment = Moment;

Vue.use(PortalVue, { portalName: 'v-portal' })
Vue.use(VModal, { componentName: 'v-modal' })
Vue.use(Vuex);
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

import './components/ToastBus';
import './components/portals/Portals';
import './components/stacks/Stacks';
import './components/ProgressBar';
import './components/DirtyState';
import './components/Config';
import './components/Preference';
import './components/Permission';


import GlobalSearch from './components/GlobalSearch.vue';
import GlobalSiteSelector from './components/GlobalSiteSelector.vue';
import Login from './components/login/login';
import LoginModal from './components/login/LoginModal.vue';
import BaseEntryCreateForm from './components/entries/BaseCreateForm.vue';
import BaseTermCreateForm from './components/terms/BaseCreateForm.vue';
import CreateTermButton from './components/terms/CreateTermButton.vue';
import Importer from './components/importer/importer';
import FieldsetListing from './components/fieldsets/Listing.vue';
import FieldsetCreateForm from './components/fieldsets/CreateForm.vue';
import FieldsetEditForm from './components/fieldsets/EditForm.vue';
import BlueprintListing from './components/blueprints/Listing.vue';
import BlueprintBuilder from './components/blueprints/Builder.vue';
import FormCreateForm from './components/forms/CreateForm.vue';
import FormListing from './components/forms/Listing.vue';
import FormSubmissionListing from './components/forms/SubmissionListing.vue';
import GlobalListing from './components/globals/Listing.vue';
import GlobalEditForm from './components/globals/EditForm.vue';
import GlobalPublishForm from './components/globals/PublishForm.vue';
import GlobalCreateForm from './components/globals/Create.vue';
import UserListing from './components/users/Listing.vue';
import UserWizard from './components/users/Wizard.vue';
import RoleListing from './components/roles/Listing.vue';
import RolePublishForm from './components/roles/PublishForm.vue';
import UserGroupListing from './components/user-groups/Listing.vue';
import UserGroupPublishForm from './components/user-groups/PublishForm.vue';
import CollectionCreateForm from './components/collections/CreateForm.vue';
import CollectionScaffolder from './components/collections/Scaffolder.vue';
import CollectionEditForm from './components/collections/EditForm.vue';
import CollectionView from './components/collections/View.vue';
import CollectionBlueprintListing from './components/collections/BlueprintListing.vue';
import SessionExpiry from './components/SessionExpiry.vue';
import NavigationListing from './components/navigation/Listing.vue';
import NavigationCreateForm from './components/navigation/CreateForm.vue';
import NavigationEditForm from './components/navigation/EditForm.vue';
import PreferencesEditForm from './components/preferences/EditForm.vue';
import NavigationView from './components/navigation/View.vue';
import TaxonomyCreateForm from './components/taxonomies/CreateForm.vue';
import TaxonomyEditForm from './components/taxonomies/EditForm.vue';
import TaxonomyBlueprintListing from './components/taxonomies/BlueprintListing.vue';
import AssetContainerCreateForm from './components/asset-containers/CreateForm.vue';
import AssetContainerEditForm from './components/asset-containers/EditForm.vue';
import NavBuilder from './components/nav/Builder.vue';
import Updater from './components/updater/Updater.vue';
import PortalTargets from './components/portals/PortalTargets.vue';


Statamic.app({
    el: '#statamic',

    mixins: [Toast],

    store: Statamic.$store,

    components: {
        GlobalSearch,
        GlobalSiteSelector,
        Login,
        LoginModal,
        BaseEntryCreateForm,
        BaseTermCreateForm,
        CreateTermButton,
        Importer,
        FieldsetListing,
        FieldsetCreateForm,
        FieldsetEditForm,
        BlueprintListing,
        BlueprintBuilder,
        FormCreateForm,
        FormListing,
        FormSubmissionListing,
        GlobalListing,
        GlobalEditForm,
        GlobalPublishForm,
        GlobalCreateForm,
        UserListing,
        UserWizard,
        RoleListing,
        RolePublishForm,
        UserGroupListing,
        UserGroupPublishForm,
        CollectionCreateForm,
        CollectionScaffolder,
        CollectionEditForm,
        CollectionView,
        CollectionBlueprintListing,
        SessionExpiry,
        NavigationListing,
        NavigationCreateForm,
        NavigationEditForm,
        PreferencesEditForm,
        NavigationView,
        TaxonomyCreateForm,
        TaxonomyEditForm,
        TaxonomyBlueprintListing,
        AssetContainerCreateForm,
        AssetContainerEditForm,
        NavBuilder,
        Updater,
        PortalTargets,
    },

    data: {
        navOpen: true,
        mobileNavOpen: false,
        showBanner: true,
        portals: [],
        appendedComponents: [],
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
