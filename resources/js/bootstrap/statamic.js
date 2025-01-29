import { createApp, ref, markRaw } from 'vue';
import App from './App.vue';
import { store } from '../store/store';
import axios from 'axios';
import Config from '../components/Config';
import Preferences from '../components/Preference';
import registerGlobalComponents from './components.js';
import registerFieldtypes from './fieldtypes.js';
import registerVueSelect from './vue-select/vue-select';
import useGlobalEventBus from '../composables/global-event-bus';
import useProgressBar from '../composables/progress-bar';
import useDirtyState from '../composables/dirty-state';
import VueClickAway from 'vue3-click-away';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css';
import VCalendar from 'v-calendar';
import 'v-calendar/style.css';
import Toasts from '../components/Toasts';
import Toasted from '@hoppscotch/vue-toasted';
import { useToasted } from '@hoppscotch/vue-toasted';
import '@hoppscotch/vue-toasted/style.css';
import 'vue-final-modal/style.css';
import PortalVue from 'portal-vue';
import Keys from '../components/keys/Keys';
import FieldActions from "../components/field-actions/FieldActions.js";
import Callbacks from '../components/Callbacks';
import Slugs from '../components/slugs/Manager';
import Portals from '../components/portals/Portals';
import Stacks from '../components/stacks/Stacks';
import Hooks from '../components/Hooks';
import Bard from '../components/Bard';
import Components from '../components/Components';
import FieldConditions from '../components/FieldConditions';
import Reveal from '../components/Reveal';
import Echo from '../components/Echo';

const darkMode = ref(null);
let bootingCallbacks = [];
let bootedCallbacks = [];
let components;

export default {

    booting(callback) {
        bootingCallbacks.push(callback);
    },

    booted(callback) {
        bootedCallbacks.push(callback);
    },

    get $store() {
        return store;
    },

    get $config() {
        return new Config(store);
    },

    get $preferences() {
        return this.$app.config.globalProperties.$preferences;
    },

    get $callbacks() {
        return this.$app.config.globalProperties.$callbacks;
    },

    get $hooks() {
        return this.$app.config.globalProperties.$hooks;
    },

    get $toast() {
        return this.$app.config.globalProperties.$toast;
    },

    get $conditions() {
        return this.$app.config.globalProperties.$conditions;
    },

    get $slug() {
        return this.$app.config.globalProperties.$slug;
    },

    get $bard() {
        return this.$app.config.globalProperties.$bard;
    },

    get $echo() {
        return this.$app.config.globalProperties.$echo;
    },

    get $components() {
        return components;
    },

    get darkMode() {
        return darkMode;
    },

    set darkMode(value) {
        darkMode.value = value;
    },

    get user() {
        return this.$config.get('user');
    },

    config(config) {
        store.commit('statamic/config', config);
    },

    component(name, component) {
        this.$components.register(name, component);
    },

    start() {
        this.$app = createApp(App);

        this.$app.config.silent = false;
        this.$app.config.devtools = true;

        this.$app.use(store);
        this.$app.use(PortalVue, { portalName: 'v-portal' });
        this.$app.use(VueClickAway);
        this.$app.use(FloatingVue, { disposeTimeout: 30000, distance: 10 });
        this.$app.use(VCalendar);
        this.$app.use(Toasted, {
            position: 'bottom-left',
            duration: 3500,
            theme: 'statamic',
            action: {
                text: '×',
                onClick: (e, toastObject) => {
                    toastObject.goAway(0);
                }
            }
        })

        const portals = markRaw(new Portals);

        components = new Components(this.$app);

        Object.assign(this.$app.config.globalProperties, {
            $axios: axios,
            $moment: window.moment,
            $events: useGlobalEventBus(),
            $preferences: new Preferences(store),
            $progress: useProgressBar(),
            $config: this.$config,
            $keys: new Keys,
            $fieldActions: new FieldActions,
            $conditions: new FieldConditions,
            $callbacks: new Callbacks,
            $dirty: useDirtyState(),
            $slug: new Slugs,
            $portals: portals,
            $stacks: new Stacks(portals),
            $hooks: new Hooks,
            $toast: new Toasts(useToasted()),
            $bard: new Bard,
            $reveal: new Reveal,
            $echo: new Echo,
        });

        Object.assign(this.$app.config.globalProperties, {
            __(key, replacements) {
                return __(key, replacements);
            },
            __n(key, number, replacements) {
                return __n(key, number, replacements);
            },
            $markdown(value) {
                return markdown(value);
            },
            can(permission) {
                const permissions = JSON.parse(atob(Statamic.$config.get('permissions')));

                return permissions.includes('super') || permissions.includes(permission);
            },
            $wait(ms) {
                return new Promise(resolve => {
                    setTimeout(resolve, ms);
                });
            }
        });

        registerGlobalComponents(this.$app);
        registerFieldtypes(this.$app);
        registerVueSelect(this.$app);

        // Suppress the translation warnings
        this.$app.config.warnHandler = (msg, vm, trace) => {
            if (msg.includes('Property "__" should not start with _ which is a reserved prefix for Vue internals')) {
                return;
            }
            console.warn(msg, vm, trace);
        };

        bootingCallbacks.forEach(callback => callback(this));
        bootingCallbacks = [];

        this.$app.mount('#statamic');

        bootedCallbacks.forEach(callback => callback(this));
        bootedCallbacks = [];
    }

}
