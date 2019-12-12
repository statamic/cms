import Binding from './Binding';
const mousetrap = require('mousetrap');
require('mousetrap/plugins/global-bind/mousetrap-global-bind');

export default class GlobalBinding extends Binding {

    bindMousetrap(binding, callback) {
        mousetrap.bindGlobal(binding, callback);
    }

}
