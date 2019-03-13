import Statamic from './components/Statamic.js';
import Vue from 'vue';

Vue.config.silent = false;
Vue.config.devtools = true;
Vue.config.productionTip = false

window.Statamic = Statamic;
window.Vue = Vue;
window._ = require('underscore');
window.$ = window.jQuery = require('jquery');
window.rangy = require('rangy');
window.EQCSS = require('eqcss');

require('./bootstrap/globals');
require('./bootstrap/polyfills');
require('./bootstrap/underscore-mixins');
require('./bootstrap/jquery-plugins');
require('./bootstrap/redactor-plugins');
require('./bootstrap/plugins');
require('./bootstrap/filters');
require('./bootstrap/mixins');
require('./bootstrap/components');
require('./bootstrap/fieldtypes');
require('./bootstrap/directives');
