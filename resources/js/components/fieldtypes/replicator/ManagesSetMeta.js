import ManagesRowMeta from '../grid/ManagesRowMeta';

export default {

    mixins: [ManagesRowMeta],

    methods: {
        updateSetMeta(set, value) {
            this.updateRowMeta(set, value);
        },

        removeSetMeta(set) {
            this.removeRowMeta(set);
        },
    }

}