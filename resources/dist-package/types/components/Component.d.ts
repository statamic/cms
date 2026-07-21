export default Component;
declare class Component {
    constructor(id: any, name: any, props: any);
    id: any;
    name: any;
    props: any;
    events: {};
    prop(prop: any, value: any): void;
    on(event: any, handler: any): void;
    destroy(): void;
}
