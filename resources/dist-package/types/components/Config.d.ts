export default class Config {
    config: import("vue").Ref<{}, {}>;
    initialize(initialConfig: any): void;
    all(): {};
    get(key: any, fallback: any): any;
    set(key: any, value: any): void;
}
