export default Components;
declare class Components {
    queue: {};
    components: import("vue").Ref<never[], never[]>;
    boot(app: any): void;
    app: any;
    register(name: any, component: any): void;
    append(name: any, { props }: {
        props: any;
    }): Component;
    get(id: any): undefined;
    has(name: any): boolean;
    getAppended(id: any): undefined;
    destroy(id: any): void;
    #private;
}
import Component from './Component';
