import { clone } from  '../../bootstrap/globals.js'
import { data_get } from  '../../bootstrap/globals.js'

export default class {
    constructor(values, jsonFields) {
        this.values = clone(values);
        this.jsonFields = clone(jsonFields || []).sort();
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
        return dottedKey.replace(/\.*(\d+)\./g, '[$1].');
    }

    missingValue(dottedKey) {
        return data_get(clone(this.values), dottedKey) === null;
    }

    jsonDecodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let decodedFieldValue = JSON.parse(data_get(values, dottedKey));
        let jsPath = this.dottedKeyToJsPath(dottedKey);

        eval('values.' + jsPath + ' = decodedFieldValue');

        this.values = values;
    }

    jsonEncodeValue(dottedKey) {
        if (this.missingValue(dottedKey)) return;

        let values = clone(this.values);
        let encodedFieldValue = JSON.stringify(data_get(values, dottedKey));
        let jsPath = this.dottedKeyToJsPath(dottedKey);

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
