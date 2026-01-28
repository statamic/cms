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
            const { [row]: removed, ...existing } = this.meta.existing;

            this.updateMeta({ ...this.meta, existing });
        },
    },
};
