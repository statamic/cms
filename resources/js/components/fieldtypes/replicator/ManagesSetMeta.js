export default {

    methods: {
        updateSetMeta(set, value) {
            const meta = clone(this.meta);

            // If there were no existing sets, PHP would have supplied an empty
            // array, but we need it to be an object so we can assign by key.
            if (Array.isArray(meta.existing)) meta.existing = {};

            meta.existing[set] = value;
            this.updateMeta(meta);
        },

        removeSetMeta(set) {
            const meta = clone(this.meta);
            delete meta.existing[set];
            this.updateMeta(meta);
        },
    }

}