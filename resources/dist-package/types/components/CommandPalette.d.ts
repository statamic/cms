export default class CommandPalette {
    get category(): {
        Actions: string;
        Miscellaneous: string;
    };
    preventIf(fn: any): void;
    shouldPreventOpening(): boolean;
    add(command: any): any;
    clear(): void;
    actions(): any[];
    misc(): any[];
    #private;
}
