export default class Keys {
    bindings: {};
    globals: {};
    bind(bindings: any, callback: any): Binding;
    stop(callback: any): void;
    bindGlobal(bindings: any, callback: any): GlobalBinding;
}
import Binding from './Binding';
import GlobalBinding from './GlobalBinding';
