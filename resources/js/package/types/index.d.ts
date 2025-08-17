declare module '@statamic/cms' {

    export function exampleFunction(handle: string, callback: (item: any) => void): void;

    export const exampleVersion: string;

    export const exampleConfig: {
        foo: string;
        bar: number;
    }
}
