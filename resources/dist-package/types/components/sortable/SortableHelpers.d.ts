declare namespace _default {
    namespace methods {
        function newSortableValue(value?: null, key?: null): SortableKeyValue;
        function objectToSortable(obj: any): SortableKeyValue[];
        function arrayToSortable(arr: any): any;
        function sortableToObject(sortable: any): {};
        function sortableToArray(sortable: any): any[];
    }
}
export default _default;
import SortableKeyValue from './SortableKeyValue.js';
