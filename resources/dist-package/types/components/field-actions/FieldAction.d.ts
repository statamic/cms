export default class FieldAction {
    constructor(action: any, payload: any);
    title: any;
    get visible(): any;
    get disabled(): any;
    get quick(): any;
    get dangerous(): any;
    get icon(): any;
    run(): Promise<void>;
    #private;
}
