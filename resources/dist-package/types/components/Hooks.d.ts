export default Hooks;
declare class Hooks {
    hooks: {};
    on(key: any, callback: any, priority?: number): void;
    run(key: any, payload: any): Promise<any>;
    getCallbacks(key: any): any;
    convertToPromiseCallback(callback: any, payload: any): () => Promise<any>;
}
