import { createApp } from 'vue';
import { store } from '../store.js';
import eventBus from '../eventBus.js';
import http from '../http';
import Config from './Config';
import Preferences from './Preference';
import * as GlobalComponents from './GlobalComponents.js';

import Echo from './Echo';
import Bard from './Bard';
import Keys from './keys/Keys';
import Hooks from './Hooks';
import Reveal from './Reveal';
import Components from './Components';
import FieldConditions from './FieldConditions';
import Callbacks from './Callbacks.js';
import Slugs from './slugs/Manager.js';
import * as Globals from "../bootstrap/globals.js";
// import isLatLong from "validator/es/lib/isLatLong.js";

const echo = new Echo;
const bard = new Bard;
const keys = new Keys;
const hooks = new Hooks;
const reveal = new Reveal;
const components = new Components;
const conditions = new FieldConditions;
const callbacks = new Callbacks;
const slug = new Slugs;

// Packages used by Statamic
import PortalVue from 'portal-vue';
import FloatingVue from 'floating-vue';
import VueClickAway from 'vue3-click-away';
import vClickOutside from "click-outside-vue3";
import VueSelect from "vue-select";

export default {
    bootingCallbacks: [],
    bootedCallbacks: [],

    $callbacks() {
        return callbacks;
    },

    $components() {
        return components;
    },

    $request() {
        // TODO: Custom $request error handling, etc?  For now, we'll just alias directly to $axios.
        return this.$axios;
    },

    $echo() {
        return echo;
    },

    $bard() {
        return bard;
    },

    $hooks() {
        return hooks;
    },

    $reveal() {
        return reveal;
    },

    $conditions() {
        return conditions;
    },

    $keys() {
        return keys;
    },

    // $preferences() {
    //     return preferences;
    // },

    $slug() {
        return slug;
    },

    user() {
        return this.$config.get('user');
    },

    booting(callback) {
        this.bootingCallbacks.push(callback);
    },

    booted(callback) {
        this.bootedCallbacks.push(callback);
    },

    get $config() {
        return new Config(store);
    },

    config(config) {
        store.commit('statamic/config', config);
    },

    start() {
        // Create Vue 3 app
        this.$app = createApp({
            data() {
                return {
                    navOpen: true,
                    mobileNavOpen: false,
                    showBanner: false, // TODO ENABLE THIS
                    portals: [],
                    appendedComponents: [],
                }
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
                },

                // setupMoment() {
                //     const locale = Statamic.$config.get('locale');
                //     window.moment.locale(locale);
                //     Vue.moment.locale(locale);
                //     Vue.prototype.$moment.locale(locale);
                //
                //     const spec = {
                //         relativeTime: {
                //             future: __('moment.relativeTime.future'),
                //             past: __('moment.relativeTime.past'),
                //             s: __('moment.relativeTime.s'),
                //             ss: __('moment.relativeTime.ss'),
                //             m: __('moment.relativeTime.m'),
                //             mm: __('moment.relativeTime.mm'),
                //             h: __('moment.relativeTime.h'),
                //             hh: __('moment.relativeTime.hh'),
                //             d: __('moment.relativeTime.d'),
                //             dd: __('moment.relativeTime.dd'),
                //             M: __('moment.relativeTime.M'),
                //             MM: __('moment.relativeTime.MM'),
                //             y: __('moment.relativeTime.y'),
                //             yy: __('moment.relativeTime.yy'),
                //         }
                //     };
                //     window.moment.updateLocale(locale, spec);
                //     Vue.moment.updateLocale(locale, spec);
                //     Vue.prototype.$moment.updateLocale(locale, spec);
                // }
            }
        });
        this.$app.use(store);
        this.$app.use(PortalVue);
        this.$app.use(FloatingVue);
        this.$app.use(VueClickAway);
        this.$app.use(vClickOutside);

        // Create event bus because of the old events of Vue 2
        this.$app.config.globalProperties.$events = eventBus;

        // Add classes
        this.$app.config.globalProperties.$axios = http;
        this.$app.config.globalProperties.$keys = keys;
        this.$app.config.globalProperties.$config = this.$config;
        this.$app.config.globalProperties.$preferences = new Preferences(http, store);

        // Assign mixins
        Object.assign(this.$app.config.globalProperties, {
            __(key, replacements) {
                return __(key, replacements);
            },
            __n(key, number, replacements) {
                return __n(key, number, replacements);
            },
            translate(key, replacements) { // TODO: Remove
                return __(key, replacements);
            },
            $wait(ms) {
                return new Promise(resolve => {
                    setTimeout(resolve, ms);
                });
            }
        });

        // Load all global components
        Object.keys(GlobalComponents).forEach(key => {
            this.$app.component(key, GlobalComponents[key]);
        });

        this.$app.component('v-select', VueSelect);

        this.bootingCallbacks.forEach(callback => callback(this));
        this.bootingCallbacks = [];

        // Mount app
        this.$app.mount('#statamic');

        // this.$components.$root = this.$app;

        this.bootedCallbacks.forEach(callback => callback(this));
        this.bootedCallbacks = [];
    },

    component(name, component) {
        this.$app.component(name, component);
    }
};
