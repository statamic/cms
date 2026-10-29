export default class Portals {
    portals: import("vue").Ref<never[], never[]>;
    all(): never[];
    top(): any;
    create(name: any, data?: {}): Portal;
    isDisabled(id: any): boolean;
    setDisabled(id: any, disabled: any): void;
    destroy(id: any): void;
    stacks(): never[];
    #private;
}
import Portal from './Portal';
