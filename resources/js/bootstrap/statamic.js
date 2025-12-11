import { createApp, h } from 'vue';
import App from './App.vue';
import { createPinia, defineStore } from 'pinia';
import axios from 'axios';
import registerGlobalComponents from './components.js';
import registerGlobalCommandPalette from './commands.js';
import registerUiComponents from './ui.js';
import registerFieldtypes from './fieldtypes.js';
import VueClickAway from 'vue3-click-away';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import PortalVue from 'portal-vue';
import autosize from 'autosize';
import wait from '@/util/wait.js';
import markdown from '@/util/markdown.js';
import VueComponentDebug from 'vue-component-debug';
import { registerIconSetFromStrings } from '@ui';
import Layout from '@/pages/layout/Layout.vue';
import { setTranslations, setLocale } from '@/translations/translator.js';
import {
    keys,
    components,
    events,
    progress,
    fieldActions,
    conditions,
    callbacks,
    dirty,
    slug,
    hooks,
    bard,
    reveal,
    echo,
    permissions,
    dateFormatter,
    commandPalette,
    colorMode,
    contrast,
    config,
    preferences,
    toast,
    portals,
    stacks,
    inertia,
} from '@api';

let bootingCallbacks = [];
let bootedCallbacks = [];

export default {
    booting(callback) {
        bootingCallbacks.push(callback);
    },

    booted(callback) {
        bootedCallbacks.push(callback);
    },

    get $config() {
        return config;
    },

    get $preferences() {
        return preferences;
    },

    get $callbacks() {
        return callbacks;
    },

    get $hooks() {
        return hooks;
    },

    get $toast() {
        return toast;
    },

    get $conditions() {
        return conditions;
    },

    get $slug() {
        return slug;
    },

    get $bard() {
        return bard;
    },

    get $echo() {
        return echo;
    },

    get $pinia() {
        return { defineStore };
    },

    get $keys() {
        return keys;
    },

    get $permissions() {
        return permissions;
    },

    get $components() {
        return components;
    },

    get $inertia() {
        return inertia;
    },

    get $date() {
        return dateFormatter;
    },

    get $progress() {
        return progress;
    },

    get $colorMode() {
        return colorMode;
    },

    get $contrast() {
        return contrast;
    },

    get $fieldActions() {
        return fieldActions;
    },

    get $dirty() {
        return dirty;
    },

    get $events() {
        return events;
    },

    get $reveal() {
        return reveal;
    },

    get $commandPalette() {
        return commandPalette;
    },

    get user() {
        return this.$config.get('user');
    },

    config(config) {
        this.initialConfig = config;
    },

    component(name, component) {
        this.$components.register(name, component);
    },

    async start() {
        setTranslations(this.initialConfig.translations);
        setLocale(this.initialConfig.translationLocale);

        config.initialize(this.initialConfig);
        colorMode.initialize(this.initialConfig.user?.color_mode);
        contrast.initialize(this.initialConfig.user?.preferences?.strict_accessibility);
        preferences.initialize(this.initialConfig.user?.preferences, this.initialConfig.defaultPreferences);

        bootingCallbacks.forEach((callback) => callback(this));
        bootingCallbacks = [];

        const el = document.getElementById('statamic');
        const titleEl = document.getElementById('blade-title');
        const bladeTitle = titleEl?.dataset.title;
        const bladeContent = el?.innerHTML || '';
        const _this = this;

        await createInertiaApp({
            id: 'statamic',
            resolve: name => {
                if (name === 'NonInertiaPage') {
                    return {
                        default: {
                            layout: Layout,
                            template: `<div>${bladeContent}</div>`,
                        }
                    }
                }

                // Resolve core pages
                const pages = import.meta.glob('../pages/**/*.vue', { eager: true });
                let page = pages[`../pages/${name}.vue`];

                // Resolve addon pages
                if (!page) {
                    const addonPage = inertia.get(name);
                    if (addonPage) page = { default: addonPage };
                }

                if (!page) {
                    let message = `Couldn't find Inertia component for the [${name}] page. `;
                    message += name.endsWith('.vue')
                        ? 'You do not need to include the .vue extension when referencing a page.'
                        : 'Did you you register a [Pages/${name}] component?';
                    throw new Error(message);
                }

                page.default.layout = page.default.layout || Layout;
                return page;
            },
            async setup({ el, App: InertiaApp, props, plugin }) {
                const app = await _this.configureApp(InertiaApp, props);
                app.use(plugin).mount(el);
            },
            title: (title) => title || bladeTitle
        })

        // Handle non-Inertia responses with full page reload
        router.on('invalid', (event) => {
            if (event.detail.response.status === 200) {
                event.preventDefault();
                window.location.href = event.detail.response.request.responseURL;
            }
        });

        bootedCallbacks.forEach((callback) => callback(this));
        bootedCallbacks = [];
    },

    async configureApp(InertiaApp, props, el) {
        this.$app = createApp({
            ...App,
            render: () => h(InertiaApp, props),
        });

        this.$app.config.silent = false;
        this.$app.config.devtools = true;

        this.$app.use(createPinia());
        this.$app.use(PortalVue, { portalName: 'v-portal' });
        this.$app.use(VueClickAway);
        this.$app.use(FloatingVue, { disposeTimeout: 30000, distance: 10 });
        this.$app.use(VueComponentDebug, { enabled: import.meta.env.VITE_VUE_COMPONENT_DEBUG === 'true' });
        toast.initialize(this.$app);

        Object.assign(this.$app.config.globalProperties, {
            $config: config,
            $axios: axios,
            $events: events,
            $preferences: preferences,
            $progress: progress,
            $keys: keys,
            $fieldActions: fieldActions,
            $conditions: conditions,
            $callbacks: callbacks,
            $dirty: dirty,
            $slug: slug,
            $portals: portals,
            $stacks: stacks,
            $hooks: hooks,
            $toast: toast,
            $bard: bard,
            $reveal: reveal,
            $echo: echo,
            $permissions: permissions,
            $date: dateFormatter,
            $commandPalette: commandPalette,
            $colorMode: colorMode,
            $contrast: contrast,
        });

        Object.assign(this.$app.config.globalProperties, {
            __(key, replacements) {
                return __(key, replacements);
            },
            __n(key, number, replacements) {
                return __n(key, number, replacements);
            },
            $markdown(value, options = {}) {
                return markdown(value, options);
            },
            cp_url(url) {
                return cp_url(url);
            },
            docs_url(url) {
                return docs_url(url);
            },
            can(permission) {
                const permissions = JSON.parse(atob(Statamic.$config.get('permissions')));

                return permissions.includes('super') || permissions.includes(permission);
            },
            $wait(ms) {
                return wait(ms);
            },
        });

        this.$app.directive('elastic', {
            mounted: (el) => autosize(el),
        });

        await registerUiComponents(this.$app);
        registerGlobalComponents(this.$app);
        registerGlobalCommandPalette();
        registerFieldtypes(this.$app);
        registerIconSets(this.initialConfig);
        components.boot(this.$app);

        // Suppress the translation warnings
        this.$app.config.warnHandler = (msg, vm, trace) => {
            if (msg.includes('Property "__" should not start with _ which is a reserved prefix for Vue internals')) {
                return;
            }
            console.warn(msg, vm, trace);
        };

        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = Statamic.$config.get('csrfToken');

        return this.$app;
    },
};

function registerIconSets(config) {
    const sets = config.customSvgIcons;

    for (const name in sets) {
        registerIconSetFromStrings(name, sets[name]);
    }
}
