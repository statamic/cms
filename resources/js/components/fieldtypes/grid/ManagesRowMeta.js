export default {

    methods: {
        updateRowMeta(row, value) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [row]: value
                }
            });
        },

        removeRowMeta(row) {
            this.updateMeta({
                ...this.meta,
                existing: _.omit(this.meta.existing, row)
            });
        },
    }

}
