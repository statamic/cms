export default class ColorMode {
    initialize(preference: any): void;
    get mode(): import("vue").Ref<null, null>;
    set preference(value: any);
    get preference(): any;
    #private;
}
