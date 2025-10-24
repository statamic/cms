import Binding from './Binding';
import mousetrap from 'mousetrap';
import 'mousetrap/plugins/global-bind/mousetrap-global-bind';

export default class GlobalBinding extends Binding {
    bindMousetrap(binding, callback) {
        mousetrap.bindGlobal(binding, callback);
    }
}
