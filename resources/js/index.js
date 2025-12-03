import Statamic from './bootstrap/statamic.js';
import * as Vue from 'vue';
import * as Pinia from 'pinia';
import Alpine from 'alpinejs';
import * as Globals from './bootstrap/globals';
import Cookies from 'cookies-js';
import * as cms from './bootstrap/cms/index.js';

import.meta.glob(['../img/**']);

let global_functions = Object.keys(Globals);
global_functions.forEach((fnName) => {
    window[fnName] = Globals[fnName];
});

window.Vue = Vue;
window.Pinia = Pinia;
window.Cookies = Cookies;
window.Alpine = Alpine;
window.Statamic = Statamic;
window.__STATAMIC__ = cms;

// In prod dev builds, ensure HMR runtime is available
if (import.meta.env.MODE === 'development' || import.meta.hot) {
    // HMR runtime will be automatically set up by Vue in dev mode
    // Just ensure it's accessible globally for addons
    if (!window.__VUE_HMR_RUNTIME__) {
        // Import HMR API - only available when NODE_ENV is development
        import('vue').then(Vue => {
            // The HMR runtime is set up by @vitejs/plugin-vue
            // We just need to ensure it's on window
            window.__VUE_HMR_RUNTIME__ = window.__VUE_HMR_RUNTIME__ || {};
        });
    }
}

Alpine.start();
