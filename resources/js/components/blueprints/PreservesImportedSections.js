// Imported fieldset sections are only kept when the import sits at the top level of a blueprint.
// Fieldsets and nested fields (e.g. replicator sets, grids) always flatten them.
export default {
    inject: {
        importIsInsideSet: { from: 'isInsideSet', default: false },
        importIsInsideConfigFields: { from: 'isInsideConfigFields', default: false },
        importIsInsideFieldset: { from: 'isInsideFieldset', default: false },
    },

    computed: {
        canPreserveImportedSections() {
            return !this.importIsInsideSet && !this.importIsInsideConfigFields && !this.importIsInsideFieldset;
        },
    },
};
