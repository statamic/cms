export default class {
    constructor(currentFieldPath) {
        this.currentFieldPath = currentFieldPath;
    }

    resolve(pathWithParent) {
        let parentPath = this.getParentFieldPath(this.currentFieldPath, true);
        let fieldPath = this.removeOneParentKeyword(pathWithParent);

        while (fieldPath.startsWith('$parent.')) {
            parentPath = this.getParentFieldPath(parentPath);
            fieldPath = this.removeOneParentKeyword(fieldPath);
        }

        let resolved = parentPath ? `${parentPath}.${fieldPath}` : fieldPath;

        return `$root.${resolved}`;
    }

    getParentFieldPath(dottedFieldPath, removeCurrentField) {
        const regex = new RegExp('(.*?[^\\.]+)(\\.[0-9]+)*\\.[^\\.]*$');

        if (removeCurrentField || this.isAtSetLevel(dottedFieldPath)) {
            dottedFieldPath = dottedFieldPath.replace(regex, '$1');
        }

        return dottedFieldPath.includes('.') ? dottedFieldPath.replace(regex, '$1$2') : '';
    }

    isAtSetLevel(dottedFieldPath) {
        return dottedFieldPath.match(new RegExp('(\\.[0-9]+)$'));
    }

    removeOneParentKeyword(dottedFieldPath) {
        return dottedFieldPath.replace(new RegExp('^\\$parent.'), '');
    }
}
