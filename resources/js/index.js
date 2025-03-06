import Statamic from './bootstrap/statamic.js';
import * as Vue from 'vue';
import Alpine from 'alpinejs';
import * as Globals from './bootstrap/globals';
import Cookies from 'cookies-js';
import Moment from 'moment';

import.meta.glob(['../img/**']);

let global_functions = Object.keys(Globals);
global_functions.forEach((fnName) => {
    window[fnName] = Globals[fnName];
});

window.Vue = Vue;
window.Cookies = Cookies;
window.Alpine = Alpine;
window.Statamic = Statamic;
window.moment = Moment;

Alpine.start();
