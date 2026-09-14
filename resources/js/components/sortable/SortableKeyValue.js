import { nanoid as uniqid } from 'nanoid';

export default class SortableKeyValue {
    constructor(key = null, value = null) {
        this._id = uniqid();
        this.key = key;
        this.value = value;
    }
}
