export default Bard;
declare class Bard {
    constructor(instance: any);
    instance: any;
    extensionCallbacks: any[];
    extensionReplacementCallbacks: any[];
    buttonCallbacks: any[];
    addExtension(callback: any): void;
    replaceExtension(name: any, callback: any): void;
    buttons(callback: any): void;
}
