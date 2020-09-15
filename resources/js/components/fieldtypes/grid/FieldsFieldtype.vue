<template>

    <fields
        :fields="fields"
        :editing-field="editingField"
        :can-define-localizable="false"
        @field-created="fieldCreated"
        @field-updated="fieldUpdated"
        @field-linked="fieldLinked"
        @field-deleted="deleteField"
        @field-editing="editingField = $event"
        @editor-closed="editingField = null"
    />

</template>

<script>
import Fields from '../../blueprints/Fields.vue';
import {Sortable, Plugins} from '@shopify/draggable';

export default {

    mixins: [Fieldtype],

    components: {
        Fields,
    },

    data() {
        return {
            fields: this.value,
            editingField: null,
        }
    },

    mounted() {
        this.makeSortable();
    },

    methods: {

        fieldCreated(field) {
            this.fields.push(field);
            this.update(this.fields);
        },

        fieldUpdated(i, field) {
            this.fields.splice(i, 1, field);
            this.update(this.fields);
        },

        deleteField(i) {
            this.fields.splice(i, 1);
            this.update(this.fields);
        },

        fieldLinked(field) {
            this.fields.push(field);
            this.$toast.success(__('Field added'));

            if (field.type === 'reference') {
                this.$nextTick(() => this.editingField = field._id);
            }
        },

        makeSortable() {
            new Sortable(this.$el.querySelector('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true },
                plugins: [Plugins.SwapAnimation]
            }).on('sortable:stop', e => {
                this.fields.splice(e.newIndex, 0, this.fields.splice(e.oldIndex, 1)[0]);
                this.update(this.fields);
            });
        }
    }

}
</script>
