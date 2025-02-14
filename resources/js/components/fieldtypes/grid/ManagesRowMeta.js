import { omit } from 'lodash-es';

export default {
    methods: {
        updateRowMeta(row, value) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [row]: clone(value),
                },
            });
        },

        removeRowMeta(row) {
            this.updateMeta({
                ...this.meta,
                existing: omit(this.meta.existing, row),
            });
        },
    },
};
