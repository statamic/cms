import { config } from '@vue/test-utils';
import * as Globals from '@/bootstrap/globals.js';
import { bard, dirty, events, keys, toast, progress, fieldActions, conditions, preferences } from '@api';
import perf from '@/util/perf.js';

Object.keys(Globals).forEach((fn) => {
    window[fn] = Globals[fn];
});

window.__ = (key) => key;
window.__n = (key) => key;
window.cp_url = (url) => `/cp/${url}`;
window.docs_url = (url) => `https://statamic.dev/${url}`;

const components = new Map();

const $components = {
    has: (name) => components.has(name),
    register: (name, component) => components.set(name, component),
    get: (name) => components.get(name),
};

const $config = {
    get: (key) => {
        if (key === 'sites') return [{ handle: 'default', direction: 'ltr' }];
        if (key === 'locale') return 'en';
        return undefined;
    },
};

const $commandPalette = { preventIf: () => {} };
const $axios = {
    post: async () => ({ data: { new: {}, defaults: {} } }),
    get: async () => ({ data: {} }),
};

window.Statamic = {
    $app: {
        component: (name) => components.get(name),
        config: { performance: false },
    },
    $components,
    $config,
    $dirty: dirty,
    $events: events,
    $toast: toast,
    $keys: keys,
    $commandPalette,
    $fieldActions: fieldActions,
    $permissions: { has: () => true },
    $preferences: preferences,
    $hooks: { run: async (_name, payload) => payload },
    $callbacks: { call: () => {} },
    $slug: { create: () => ({ create: () => {}, destroy: () => {} }) },
    $conditions: conditions,
    $progress: progress,
    $perf: perf,
    $bard: bard,
    $axios,
    user: { id: 'bench-user' },
};

perf.attachVueApp(window.Statamic.$app);
perf.enable();
perf.reset();

config.global.directives = {
    tooltip: () => {},
    elastic: () => {},
};

// Keep Vue-reserved `$…` keys off `mocks` — VTU assigns mocks onto the instance
// and reserved names (e.g. `$components`) throw in browser mode.
config.global.mocks = {
    __: (key) => key,
    __n: (key) => key,
    $markdown: (value) => value,
    can: () => true,
    cp_url: (url) => `/cp/${url}`,
    docs_url: (url) => `https://statamic.dev/${url}`,
    $bard: bard,
    $keys: keys,
    $toast: toast,
    $events: events,
    $dirty: dirty,
    $config,
    $commandPalette,
    $fieldActions: fieldActions,
    $conditions: conditions,
    $progress: progress,
    $preferences: preferences,
    $perf: perf,
    $axios,
};
