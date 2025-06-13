import Statamic from './bootstrap/statamic.js';
import * as Vue from 'vue';
import * as Pinia from 'pinia';
import Alpine from 'alpinejs';
import * as Globals from './bootstrap/globals';
import Cookies from 'cookies-js';

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

Alpine.start();
