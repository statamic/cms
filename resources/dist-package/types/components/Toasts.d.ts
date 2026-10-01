export default class Toasts {
    initialize(app: any): void;
    registerInterceptor(axios: any): void;
    displayInitialToasts(): void;
    success(message: any, opts: any): Promise<void>;
    info(message: any, opts: any): Promise<void>;
    error(message: any, opts: any): Promise<void>;
    #private;
}
