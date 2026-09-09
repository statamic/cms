export default class Portals {
    portals: import("vue").Ref<never[], never[]>;
    all(): never[];
    create(name: any, data?: {}): Portal;
    destroy(id: any): void;
    stacks(): never[];
}
import Portal from './Portal';
