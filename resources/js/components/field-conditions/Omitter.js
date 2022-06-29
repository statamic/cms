import { clone } from  '../../bootstrap/globals.js'

export default class {
    constructor(values, jsonFields) {
        this.values = clone(values);

        this.jsonFields = clone(jsonFields || [])
            .filter((field, index) => jsonFields.indexOf(field) === index)
            .sort();
    }

    omit(hiddenKeys) {
        this.jsonDecode()
            .omitHiddenFields(hiddenKeys)
            .jsonEncode();

        return this.values;
    }

    jsonDecode() {
        this.jsonFields.forEach(dottedKey => {
            this.jsonDecodeValue(dottedKey);
        });

        return this;
    }

    omitHiddenFields(hiddenKeys) {
        hiddenKeys.forEach(dottedKey => {
            this.forgetValue(dottedKey);
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
        return dottedKey.replace(/\.*\.(\d+)\./g, '[$1].');
    }

    missingValue(dottedKey) {
        var properties = Array.isArray(dottedKey) ? dottedKey : dottedKey.split('.');
        var value = properties.reduce((prev, curr) => prev && prev[curr], clone(this.values));

        return value === undefined;
    }

    jsonDecodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath(dottedKey);
        let fieldValue = eval('values.' + jsPath);
        let decodedFieldValue = JSON.parse(fieldValue);

        eval('values.' + jsPath + ' = decodedFieldValue');

        this.values = values;
    }

    jsonEncodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath(dottedKey);
        let fieldValue = eval('values.' + jsPath);
        let encodedFieldValue = JSON.stringify(fieldValue);

        eval('values.' + jsPath + ' = encodedFieldValue');

        this.values = values;
    }

    forgetValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let jsPath = this.dottedKeyToJsPath(dottedKey);

        eval('delete values.' + jsPath);

        this.values = values;
    }
}
