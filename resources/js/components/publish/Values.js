import { clone } from  '../../bootstrap/globals.js'
import { data_get } from  '../../bootstrap/globals.js'
import { data_set } from  '../../bootstrap/globals.js'
import isObject from 'underscore/modules/isObject.js'

export default class Values {
    constructor(values, jsonFields) {
        this.values = clone(values);

        this.jsonFields = clone(jsonFields || [])
            .filter((field, index) => jsonFields.indexOf(field) === index)
            .sort();
    }

    get(dottedKey) {
        let decodedValues = new this.constructor(clone(this.values), this.jsonFields)
            .jsonDecode()
            .values;

        return data_get(decodedValues, dottedKey);
    }

    set(dottedKey, value)  {
        this.jsonDecode()
            .setValue(dottedKey, value)
            .jsonEncode();

        return this;
    }

    except(dottedKeys) {
        return this.jsonDecode()
            .rejectValuesByKey(dottedKeys)
            .jsonEncode()
            .all();
    }

    all() {
        return this.values;
    }

    jsonDecode() {
        this.jsonFields.forEach(dottedKey => {
            this.jsonDecodeValue(dottedKey);
        });

        return this;
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

    setValue(dottedKey, value) {
        data_set(this.values, dottedKey, value);

        return this;
    }

    rejectValuesByKey(dottedKeys) {
        dottedKeys.forEach(dottedKey => {
            this.forgetValue(dottedKey);
        });

        return this;
    }

    forgetValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath('values.' + dottedKey);

        eval('delete ' + jsPath);

        this.values = values;
    }
}
