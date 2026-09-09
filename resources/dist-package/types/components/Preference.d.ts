export default class Preference {
    initialize(preferences: any, defaults: any): void;
    all(): any;
    get(key: any, fallback: any): any;
    set(key: any, value: any): any;
    append(key: any, value: any): any;
    has(key: any): any;
    remove(key: any, value?: null, cleanup?: boolean): any;
    removeValue(key: any, value: any): any;
    commitOnSuccessAndReturnPromise(promise: any): any;
    defaults(): any;
    getDefault(key: any, fallback: any): any;
    hasDefault(key: any): boolean;
    #private;
}
