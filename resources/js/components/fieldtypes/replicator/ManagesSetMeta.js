import ManagesRowMeta from '../grid/ManagesRowMeta';

export default {
    mixins: [ManagesRowMeta],

    methods: {
        updateSetMeta(set, value, previews) {
            this.updateRowMeta(set, value, previews);
        },

        removeSetMeta(set) {
            this.removeRowMeta(set);
        },
    },
};
