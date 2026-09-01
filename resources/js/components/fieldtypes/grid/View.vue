<script>
export default {
    props: ['fields', 'rows', 'meta', 'name', 'canDeleteRows', 'canAddRows', 'allowFullscreen', 'hideDisplay', 'errors', 'readOnly'],

    inject: {
        grid: 'grid',
        sectionRowSortable: { default: null },
    },

    data() {
        return {
            errorsById: {},
            sectionRowZoneEl: null,
        };
    },

    computed: {
        usesSectionRowSortable() {
            return !!this.sectionRowSortable && this.grid.isReorderable;
        },

        sortableItemClass() {
            return this.usesSectionRowSortable
                ? this.sectionRowSortable.itemClass
                : `${this.name}-sortable-item`;
        },

        sortableHandleClass() {
            return this.usesSectionRowSortable
                ? this.sectionRowSortable.handleClass
                : `${this.name}-drag-handle`;
        },

        showsHeadersInSection() {
            return !!this.grid.config.headers_in_section;
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
        rows() {
            this.$nextTick(() => this.registerSectionRowZone());
        },
    },

    mounted() {
        this.$nextTick(() => this.registerSectionRowZone());
    },

    unmounted() {
        this.unregisterSectionRowZone();
    },

    methods: {
        rowHasError(id) {
            if (Object.keys(this.errorsById).length === 0) {
                return false;
            }

            return this.errorsById.hasOwnProperty(id) && this.errorsById[id].length > 0;
        },

        registerSectionRowZone() {
            if (!this.usesSectionRowSortable || !this.$refs.zone) return;

            this.sectionRowZoneEl = this.$refs.zone;
            this.sectionRowSortable.register(this.sectionRowZoneEl, this.name);
        },

        unregisterSectionRowZone() {
            if (!this.sectionRowSortable || !this.sectionRowZoneEl) return;

            this.sectionRowSortable.unregister(this.sectionRowZoneEl);
            this.sectionRowZoneEl = null;
        },
    },
};
</script>
