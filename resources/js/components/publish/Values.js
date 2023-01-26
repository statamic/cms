import { clone } from  '../../bootstrap/globals.js'
import { data_get } from  '../../bootstrap/globals.js'
import { data_set } from  '../../bootstrap/globals.js'
import isObject from 'underscore/modules/isObject.js'

export default class {
    constructor(values, jsonFields) {
        this.values = clone(values);

        this.jsonFields = clone(jsonFields || [])
            .filter((field, index) => jsonFields.indexOf(field) === index)
            .sort();
    }

    except(dottedKeys) {
        this.jsonDecode()
            .rejectFieldsByKey(dottedKeys)
            .jsonEncode();

        return this.values;
    }

    only(dottedKeys) {
        this.jsonDecode()
            .filterFieldsByKey(dottedKeys)
            .jsonEncode();

        return this.values;
    }

    merge(newValues) {
        this.jsonDecode()
            .deepMergeIntoValues(newValues)
            .jsonEncode();

        return this.values;
    }

    get() {
        return this.values;
    }

    jsonDecode() {
        this.jsonFields.forEach(dottedKey => {
            this.jsonDecodeValue(dottedKey);
        });

        return this;
    }

    rejectFieldsByKey(dottedKeys) {
        dottedKeys.forEach(dottedKey => {
            this.forgetValue(dottedKey);
        });

        return this;
    }

    filterFieldsByKey(dottedKeys) {
        var newValues = {};

        dottedKeys.forEach(dottedKey => {
            data_set(newValues, dottedKey, data_get(this.values, dottedKey));
        });

        this.values = newValues;

        return this;
    }

    deepMergeIntoValues(newValues) {
        let decodedNewValues = new this.constructor(newValues, this.jsonFields)
            .jsonDecode()
            .get();

        this.values = this.deepMergeObjects(clone(this.values), decodedNewValues);

        return this;
    }

    deepMergeObjects(target, ...sources) {
        if (! sources.length) {
            return target;
        }

        const source = sources.shift();

        if (isObject(target) && isObject(source)) {
            for (const key in source) {
                if (isObject(source[key])) {
                    if (! target[key]) Object.assign(target, {[key]: {}});
                    this.deepMergeObjects(target[key], source[key]);
                } else {
                    Object.assign(target, {[key]: source[key]});
                }
            }
        }

        return this.deepMergeObjects(target, ...sources);
    }

    jsonEncode() {
        clone(this.jsonFields).reverse().forEach(dottedKey => {
            this.jsonEncodeValue(dottedKey);
        });

        return this;
    }

    dottedKeyToJsPath(dottedKey) {
        return dottedKey.split('.')
            .map(key => new RegExp(/^\d+.*/).test(key) ? '["' + key + '"]' : key)
            .join('.')
            .replace(/\.\[/g, '[');
    }

    missingValue(dottedKey) {
        var properties = Array.isArray(dottedKey) ? dottedKey : dottedKey.split('.');
        var value = properties.reduce((prev, curr) => prev && prev[curr], clone(this.values));

        return value === undefined;
    }

    jsonDecodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath('values.' + dottedKey);
        let fieldValue = eval(jsPath);
        let decodedFieldValue = JSON.parse(fieldValue);

        eval(jsPath + ' = decodedFieldValue');

        this.values = values;
    }

    jsonEncodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath('values.' + dottedKey);
        let fieldValue = eval(jsPath);
        let encodedFieldValue = JSON.stringify(fieldValue);

        eval(jsPath + ' = encodedFieldValue');

        this.values = values;
    }

    forgetValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath('values.' + dottedKey);

        eval('delete ' + jsPath);

        this.values = values;
    }
}
