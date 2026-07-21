export default class Callbacks {
    callbacks: any[];
    add(name: any, callback: any): void;
    call(name: any, ...args: any[]): any;
}
