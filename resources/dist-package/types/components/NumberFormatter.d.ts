export default class NumberFormatter {
    static format(number: any, options: any): any;
    static formatRange(start: any, end: any, options: any): any;
    static set defaultLocale(locale: any);
    static get defaultLocale(): any;
    constructor(number: any, options: any);
    number(value: any): NumberFormatter;
    options(options: any): NumberFormatter;
    toString(): any;
    format(number: any, options: any): any;
    formatRange(start: any, end: any, options: any): any;
    withLocale(locale: any, callback: any): any;
    setDefaultLocale(locale: any): void;
    get locale(): any;
    #private;
}
