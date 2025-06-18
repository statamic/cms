export default {
    methods: {
        updateRowMeta(row, value, previews) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [row]: clone(value),
                },
                previews: previews ? { ...this.meta.previews, [row]: previews } : this.meta.previews,
            });
        },

        removeRowMeta(row) {
            const { [row]: removed, ...existing } = this.meta.existing;

            this.updateMeta({ ...this.meta, existing });
        },
    },
};
