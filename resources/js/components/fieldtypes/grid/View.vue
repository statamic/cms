<script>
export default {
    props: ['fields', 'rows', 'meta', 'name', 'canDeleteRows', 'canAddRows', 'allowFullscreen', 'hideDisplay', 'errors'],

    inject: ['grid'],

    data() {
        return {
            errorsById: {},
        };
    },

    computed: {
        sortableItemClass() {
            return `${this.name}-sortable-item`;
        },

        sortableHandleClass() {
            return `${this.name}-drag-handle`;
        },

        fieldPathPrefix() {
            return this.grid.fieldPathPrefix ? `${this.grid.fieldPathPrefix}.${this.grid.handle}` : this.grid.handle;
        },

        metaPathPrefix() {
            return this.grid.metaPathPrefix ? `${this.grid.metaPathPrefix}.${this.grid.handle}` : this.grid.handle;
        },
    },

    provide() {
        return {
            sortableItemClass: this.sortableItemClass,
            sortableHandleClass: this.sortableHandleClass,
        };
    },

    watch: {
        errors: {
            immediate: true,
            handler(errors) {
                this.errorsById = Object.entries(errors).reduce((acc, [key, value]) => {
                    if (!key.startsWith(this.fieldPathPrefix)) {
                        return acc;
                    }

                    const subKey = key.replace(`${this.fieldPathPrefix}.`, '');
                    const rowIndex = subKey.split('.').shift();
                    const rowId = this.rows[rowIndex]?._id;

                    if (rowId) {
                        acc[rowId] = value;
                    }

                    return acc;
                }, {});
            },
        },
    },

    methods: {
        rowHasError(id) {
            if (Object.keys(this.errorsById).length === 0) {
                return false;
            }

            return this.errorsById.hasOwnProperty(id) && this.errorsById[id].length > 0;
        }
    },
};
</script>
