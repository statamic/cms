import { nanoid as uniqid } from 'nanoid';

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

        duplicateValues(values, meta) {
            const ids = {};

            const regenerateValueIds = (value) => {
                if (Array.isArray(value)) return value.map(regenerateValueIds);

                if (value === null || typeof value !== 'object') return value;

                const row = Object.fromEntries(
                    Object.entries(value).map(([key, item]) => [key, regenerateValueIds(item)]),
                );

                if (row._id) row._id = ids[row._id] = uniqid();

                if (row.type === 'set' && row.attrs?.id) row.attrs.id = ids[row.attrs.id] = uniqid();

                return row;
            };

            const regenerateMetaIds = (value) => {
                if (Array.isArray(value)) {
                    return value.map((item) => {
                        if (typeof item === 'string') return ids[item] ?? item;

                        return regenerateMetaIds(item);
                    });
                }

                if (value === null || typeof value !== 'object') return value;

                return Object.fromEntries(
                    Object.entries(value).map(([key, item]) => [ids[key] ?? key, regenerateMetaIds(item)]),
                );
            };

            return { values: regenerateValueIds(values), meta: regenerateMetaIds(meta) };
        },
    },
};
