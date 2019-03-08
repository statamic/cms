import SortableKeyValue from './SortableKeyValue.js';

export default {
    methods: {
        newSortableValue(value=null, key=null) {
            return new SortableKeyValue(key, value);
        },

        objectToSortable(obj) {
            return _.map(clone(obj), (value, key) => new SortableKeyValue(key, value));
        },

        sortableToObject(sortable) {
            let obj = {};

            _.each(sortable, sortableKeyValue => obj[sortableKeyValue.key] = sortableKeyValue.value);

            return obj;
        }
    }
}
