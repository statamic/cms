<template>

    <fields
        :fields="fields"
        :editing-field="editingField"
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
            this.$notify.success(__('Field added.'));

            if (field.type === 'reference') {
                this.$nextTick(() => this.editingField = field._id);
            }
        },

    }

}
</script>
