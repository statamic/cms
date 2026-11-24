export default class Values {
    constructor(values: any, jsonFields: any);
    values: any;
    jsonFields: any;
    get(dottedKey: any): any;
    set(dottedKey: any, value: any): this;
    mergeDottedKeys(dottedKeys: any, values: any): this;
    except(dottedKeys: any): any;
    all(): any;
    jsonDecode(): this;
    jsonEncode(): this;
    dottedKeyToJsPath(dottedKey: any): any;
    missingValue(dottedKey: any): boolean;
    jsonDecodeValue(dottedKey: any): void;
    jsonEncodeValue(dottedKey: any): void;
    setValue(dottedKey: any, value: any): this;
    rejectValuesByKey(dottedKeys: any): this;
    forgetValue(dottedKey: any): void;
}
