import SortableKeyValue from './SortableKeyValue.js';
import { each } from 'lodash-es';

export default {
    methods: {
        newSortableValue(value = null, key = null) {
            return new SortableKeyValue(key, value);
        },

        objectToSortable(obj) {
            return Object.entries(clone(obj)).map(([key, value]) => new SortableKeyValue(key, value));
        },

        arrayToSortable(arr) {
            return clone(arr).map((value) => new SortableKeyValue(null, value));
        },

        sortableToObject(sortable) {
            let obj = {};

            each(sortable, (sortableKeyValue) => (obj[sortableKeyValue.key] = sortableKeyValue.value));

            return obj;
        },

        sortableToArray(sortable) {
            let arr = [];

            each(sortable, (sortableKeyValue) => arr.push(sortableKeyValue.value));

            return arr;
        },
    },
};
