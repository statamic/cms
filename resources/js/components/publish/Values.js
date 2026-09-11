import { clone } from '../../bootstrap/globals.js';
import { data_get } from '../../bootstrap/globals.js';
import { data_set } from '../../bootstrap/globals.js';

function data_delete(obj, path) {
    var parts = path.split('.');
    while (parts.length - 1) {
        var key = parts.shift();
        if (obj === null || typeof obj !== 'object') return;
        var shouldBeArray = parts.length ? new RegExp('^[0-9]+$').test(parts[0]) : false;
        if (!(key in obj)) obj[key] = shouldBeArray ? [] : {};
        obj = obj[key];
    }
    if (obj !== null && typeof obj === 'object') delete obj[parts[0]];
}

export default class Values {
    constructor(values, jsonFields) {
        this.values = clone(values);

        this.jsonFields = clone(jsonFields || [])
            .filter((field, index) => jsonFields.indexOf(field) === index)
            .sort();
    }

    get(dottedKey) {
        let decodedValues = new this.constructor(clone(this.values), this.jsonFields).jsonDecode().values;

        return data_get(decodedValues, dottedKey);
    }

    set(dottedKey, value) {
        this.jsonDecode().setValue(dottedKey, value).jsonEncode();

        return this;
    }

    mergeDottedKeys(dottedKeys, values) {
        let decodedValues = new this.constructor(clone(values.values), values.jsonFields).jsonDecode().values;

        this.jsonDecode();
        dottedKeys.forEach((dottedKey) => {
            data_set(this.values, dottedKey, data_get(decodedValues, dottedKey));
        });
        this.jsonEncode();

        return this;
    }

    except(dottedKeys) {
        return this.jsonDecode().rejectValuesByKey(dottedKeys).jsonEncode().all();
    }

    all() {
        return this.values;
    }

    jsonDecode() {
        this.jsonFields.forEach((dottedKey) => {
            this.jsonDecodeValue(dottedKey);
        });

        return this;
    }

    jsonEncode() {
        clone(this.jsonFields)
            .reverse()
            .forEach((dottedKey) => {
                this.jsonEncodeValue(dottedKey);
            });

        return this;
    }

    dottedKeyToJsPath(dottedKey) {
        return dottedKey
            .split('.')
            .map((key) => (new RegExp(/^\d+.*/).test(key) ? '["' + key + '"]' : key))
            .join('.')
            .replace(/\.\[/g, '[');
    }

    missingValue(dottedKey) {
        var properties = Array.isArray(dottedKey) ? dottedKey : dottedKey.split('.');
        // Read-only walk — no need to clone. The constructor already made this.values private.
        var value = properties.reduce((prev, curr) => (prev == null ? undefined : prev[curr]), this.values);

        return value === undefined;
    }

    jsonDecodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let fieldValue = data_get(this.values, dottedKey);
        data_set(this.values, dottedKey, JSON.parse(fieldValue));
    }

    jsonEncodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let fieldValue = data_get(this.values, dottedKey);
        data_set(this.values, dottedKey, JSON.stringify(fieldValue));
    }

    setValue(dottedKey, value) {
        data_set(this.values, dottedKey, value);

        return this;
    }

    rejectValuesByKey(dottedKeys) {
        dottedKeys.forEach((dottedKey) => {
            this.forgetValue(dottedKey);
        });

        return this;
    }

    forgetValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        data_delete(this.values, dottedKey);
    }
}
