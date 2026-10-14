export default class DateFormatter {
    static format(date: any, options: any): any;
    static set defaultLocale(locale: any);
    static get defaultLocale(): any;
    constructor(date: any, options: any);
    date(value: any): DateFormatter;
    options(options: any): DateFormatter;
    toString(): any;
    format(date: any, options: any): any;
    withLocale(locale: any, callback: any): any;
    setDefaultLocale(locale: any): void;
    get locale(): any;
    #private;
}
