declare module '@statamic/cms' {

    // Example type definitions just to check intellisense is working. We'll update later.
    
    export function exampleFunction(handle: string, callback: (item: any) => void): void;

    export const exampleVersion: string;

    export const exampleConfig: {
        foo: string;
        bar: number;
    }
}
