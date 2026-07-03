export class Pipeline {
    provide(provided: any): this;
    through(steps: any): Promise<any>;
}
export class PipelineStopped extends Error {
    constructor(payload: any);
    payload: any;
}
export class Request extends Step {
    constructor(url: any, method: any, extraData: any);
    handle(payload: any): Promise<any>;
    #private;
}
export class BeforeSaveHooks extends Step {
    constructor(prefix: any, hookPayload: any);
    handle(payload: any): Promise<any>;
    #private;
}
export class AfterSaveHooks extends Step {
    constructor(prefix: any, hookPayload: any);
    handle(response: any): Promise<any>;
    #private;
}
declare class Step {
}
export {};
