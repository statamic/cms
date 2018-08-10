<template>

    <div :class="[
        'section-fields',
        classes
    ]">
        <fieldset-field
            v-for="(i, f) in fields"
            v-ref=fields
            :field.sync="f"
            :fieldtypes="fieldtypes"
            :section="section"
            :is-first-field="i === 0"
            :is-last-field="i === fields.length-1"
            :parent-key="parentKey"
            @removed="remove(i)"
        ></fieldset-field>

        <fieldtype-selector
            :fieldtypes="fieldtypes"
            :show="isAdding"
            :allow-title="allowAddingTitleField"
            :allow-slug="allowAddingSlugField"
            :allow-date="allowAddingDateField"
            @selected="add"
            @closed="this.$emit('selector-closed')"
        ></fieldtype-selector>
    </div>

</template>

<script>
import FieldsetField from './Field.vue';
import { Sortable } from '@shopify/draggable';

export default {

    components: { FieldsetField },

    props: {
        fields: {},
        section: {},
        parentKey: {},
        fieldtypes: {},
        classes: {},
        isAdding: {
            type: Boolean,
            default: false
        },
        isQuickAdding: {
            type: Boolean,
            default: false
        }
    },

    computed: {

        isRootLevel() {
            return this.$parent.$el.classList.contains('section-layout');
        },

        allowAddingTitleField() {
            return this.isRootLevel && !_.pluck(this.fields, 'name').includes('title');
        },

        allowAddingSlugField() {
            return this.isRootLevel && !_.pluck(this.fields, 'name').includes('slug');
        },

        allowAddingDateField() {
            return this.isRootLevel && !_.pluck(this.fields, 'name').includes('date');
        }

    },

    mounted() {
        if (this.fields == null) {
            this.fields = [];
        }

        this.sortable();
    },

    methods: {

        sortable() {
            // The root level fields have their own sortable handler
            // because they can be dragged between sections, etc.
            if (this.isRootLevel) return;

            const container = this.$el;
            const sortableFields = new Sortable(container, {
                draggable: `.section-field--${this.parentKey}`,
                handle: `.field-drag-handle--${this.parentKey}`,
                appendTo: container,
                mirror: { constrainDimensions: true },
            }).on('sortable:stop', e => {
                this.fields.splice(e.newIndex, 0, this.fields.splice(e.oldIndex, 1)[0]);
            });
        },

        add(field) {
            const fields = this.fields || [];
            const count = fields.length + 1;

            // If the field is a meta field it will have the name, id,
            // and display keys already populated at this point.
            if (! field.isMeta) {
                field.name = 'field_' + count;
                field.id = 'field_' + count;
                field.display = 'Field ' + count;
            }

            fields.push(field);
            this.fields = fields;
            this.isSelecting = false;

            this.$notify.success(translate('cp.field_added', {
                fieldtype: field.isMeta ? field.display : _.find(this.fieldtypes, { name: field.type }).label
            }));

            this.$nextTick(() => {
                const field = this.$refs.fields[count-1];
                this.isQuickAdding ? field.focus() : field.edit();
            });
        },

        updateFieldWidths() {
            if (this.$refs.fields) {
                _.each(this.$refs.fields, component => component.updateFieldWidths());
            }
        },

        remove(i) {
            if (! confirm(translate('cp.are_you_sure'))) {
                return;
            }

            this.fields.splice(i, 1);
        }

    }

}
</script>
