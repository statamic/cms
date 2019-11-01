import Vue from 'vue';
import Toast from './mixins/Toast.js';
import Statamic from './components/Statamic.js';

Vue.config.silent = false;
Vue.config.devtools = true;
Vue.config.productionTip = false

window.Vue = Vue;
window.Statamic = Statamic;
window._ = require('underscore');
window.$ = window.jQuery = require('jquery');
window.rangy = require('rangy');
window.EQCSS = require('eqcss');

require('./bootstrap/globals');
require('./bootstrap/polyfills');
require('./bootstrap/underscore-mixins');
require('./bootstrap/jquery-plugins');
require('./bootstrap/plugins');
require('./bootstrap/filters');
require('./bootstrap/mixins');
require('./bootstrap/components');
require('./bootstrap/fieldtypes');
require('./bootstrap/directives');

// import Wizard from './mixins/Wizard.js';
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

Vue.prototype.$axios = axios;
Vue.prototype.$mousetrap = require('mousetrap');
require('mousetrap/plugins/global-bind/mousetrap-global-bind');

Vue.prototype.$events = new Vue();
Vue.prototype.$echo = Statamic.$echo;
Vue.prototype.$bard = Statamic.$bard;

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
        GlobalSearch: require('./components/GlobalSearch.vue'),
        SiteSelector: require('./components/SiteSelector.vue'),
        PageTree: require('./components/structures/PageTree.vue'),
        Login: require('./components/login/login'),
        LoginModal: require('./components/login/LoginModal.vue'),
        BaseEntryCreateForm: require('./components/entries/BaseCreateForm.vue'),
        BaseTermCreateForm: require('./components/terms/BaseCreateForm.vue'),
        CreateEntryButton: require('./components/entries/CreateEntryButton.vue'),
        CreateTermButton: require('./components/terms/CreateTermButton.vue'),
        Importer: require('./components/importer/importer'),
        FieldsetListing: require('./components/fieldsets/Listing.vue'),
        FieldsetEditForm: require('./components/fieldsets/EditForm.vue'),
        BlueprintListing: require('./components/blueprints/Listing.vue'),
        BlueprintBuilder: require('./components/blueprints/Builder.vue'),
        FormListing: require('./components/forms/Listing.vue'),
        FormSubmissionListing: require('./components/forms/SubmissionListing.vue'),
        GlobalListing: require('./components/globals/Listing.vue'),
        GlobalPublishForm: require('./components/globals/PublishForm.vue'),
        GlobalCreateForm: require('./components/globals/Create.vue'),
        UserListing: require('./components/users/Listing.vue'),
        UserWizard: require('./components/users/Wizard.vue'),
        RoleListing: require('./components/roles/Listing.vue'),
        RolePublishForm: require('./components/roles/PublishForm.vue'),
        UserGroupListing: require('./components/user-groups/Listing.vue'),
        UserGroupPublishForm: require('./components/user-groups/PublishForm.vue'),
        CollectionWizard: require('./components/collections/Wizard.vue'),
        CollectionEditForm: require('./components/collections/EditForm.vue'),
        SessionExpiry: require('./components/SessionExpiry.vue'),
        StructureWizard: require('./components/structures/Wizard.vue'),
        StructureListing: require('./components/structures/Listing.vue'),
        StructureEditForm: require('./components/structures/EditForm.vue'),
        Stacks: require('./components/stacks/Stacks.vue'),
        TaxonomyWizard: require('./components/taxonomies/Wizard.vue'),
        TaxonomyEditForm: require('./components/taxonomies/EditForm.vue'),
        AssetContainerCreateForm: require('./components/asset-containers/CreateForm.vue'),
        AssetContainerEditForm: require('./components/asset-containers/EditForm.vue'),
        FormWizard: require('./components/forms/Wizard.vue'),
    },

    data: {
        showLoginModal: false,
        navOpen: true,
        modals: [],
        stacks: [],
        panes: [],
        appendedComponents: []
    },

    computed: {

        version() {
            return Statamic.$config.get('version');
        },

        computedNavOpen() {
            // if (this.stackCount > 0) return false;

            return this.navOpen;
        },

        stackCount() {
            return this.$stacks.count();
        }

    },

    mounted() {
        this.bindWindowResizeListener();

        this.$mousetrap.bind(['command+\\'], e => {
            e.preventDefault();
            this.toggleNav();
        });

        if (this.$config.get('broadcasting.enabled')) {
            this.$echo.start();
        }

        // Set moment locale
        window.moment.locale(Statamic.$config.get('locale'))
        Vue.moment.locale(Statamic.$config.get('locale'))
        Vue.prototype.$moment.locale(Statamic.$config.get('locale'))
    },

    created() {
        const state = localStorage.getItem('statamic.nav') || 'open';
        this.navOpen = state === 'open';
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
        }
    }

});

// TODO: Drag events
// TODO: Live Preview
