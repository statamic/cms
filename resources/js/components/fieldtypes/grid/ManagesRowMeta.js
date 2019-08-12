export default {

    methods: {
        updateRowMeta(row, value) {
            const meta = clone(this.meta);

            // If there were no existing rows, PHP would have supplied an empty
            // array, but we need it to be an object so we can assign by key.
            if (Array.isArray(meta.existing)) meta.existing = {};

            meta.existing[row] = value;
            this.updateMeta(meta);
        },

        removeRowMeta(row) {
            const meta = clone(this.meta);
            delete meta.existing[row];
            this.updateMeta(meta);
        },
    }

}