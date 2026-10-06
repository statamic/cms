export default class Binding {
    constructor(bindings: any);
    bindings: any;
    bind(bindings: any, callback: any): this;
    bound: any;
    destroy(): void;
    stop(callback: any): void;
    bindMousetrap(binding: any, callback: any): void;
}
