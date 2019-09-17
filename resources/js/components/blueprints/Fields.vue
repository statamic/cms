<template>

    <div class="flex flex-col">

        <div class="blueprint-section-draggable-zone flex flex-wrap flex-1 mb-1 px-1 pt-2">
            <component
                v-for="(field, i) in fields"
                :is="fieldComponent(field)"
                :key="field._id"
                :field="field"
                :is-editing="editingField === field._id"
                :is-section-expanded="isSectionExpanded"
                :suggestable-condition-fields="suggestableConditionFields"
                @edit="$emit('field-editing', field._id)"
                @updated="$emit('field-updated', i, $event)"
                @deleted="$emit('field-deleted', i)"
                @editor-closed="$emit('editor-closed')"
            />
        </div>

        <div class="p-2 pt-0 flex items-center -mx-sm">
            <div class="w-1/2 px-sm">
                <link-fields @linked="$emit('field-linked', $event)" />
            </div>
            <div class="w-1/2 px-sm">
                <button class="btn w-full flex justify-center items-center" @click="isSelectingNewFieldtype = true;">
                    <svg-icon name="wireframe" class="mr-1" />
                    {{ __('Create Field') }}
                </button>
            </div>
        </div>

        <stack name="fieldtype-selector"
            v-if="isSelectingNewFieldtype"
            @closed="isSelectingNewFieldtype = false"
        >
            <fieldtype-selector slot-scope="{ close }" @closed="close" @selected="fieldtypeSelected" />
        </stack>

        <stack name="field-settings"
            v-if="pendingCreatedField != null"
            @closed="pendingCreatedField = null"
        >
            <field-settings
                slot-scope="{ close }"
                ref="settings"
                :type="pendingCreatedField.config.type"
                :root="true"
                :config="pendingCreatedField.config"
                :suggestable-condition-fields="suggestableConditionFields"
                @committed="fieldCreated"
                @closed="close"
            />
        </stack>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import RegularField from './RegularField.vue';
import ImportField from './ImportField.vue';
import LinkFields from './LinkFields.vue';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';
import FieldSettings from '../fields/Settings.vue';

export default {

    components: {
        RegularField,
        ImportField,
        LinkFields,
        FieldtypeSelector,
        FieldSettings,
    },

    props: {
        fields: Array,
        editingField: {},
        isSectionExpanded: Boolean,
        suggestableConditionFields: Array,
    },

    data() {
        return {
            isSelectingNewFieldtype: false,
            pendingCreatedField: null,
        }
    },

    methods: {

        fieldComponent(field) {
            return (field.type === 'import') ? 'ImportField' : 'RegularField';
        },

        fieldtypeSelected(field) {
            this.isSelectingNewFieldtype = false;

            const handle = field.type;

            const pending = {
                _id: uniqid(),
                type: 'inline',
                fieldtype: field.type,
                handle,
                config: {
                    ...field,
                    isNew: true,
                    handle,
                    display: field.type,
                }
            };

            this.$nextTick(() => this.pendingCreatedField = pending);
        },

        fieldCreated(created) {
            let handle = created.handle;
            delete created.handle;

            let field = {
                ...this.pendingCreatedField,
                ...{ handle },
                config: created
            };

            this.$emit('field-created', field);

            this.$notify.success(__('Field added.'));
            this.pendingCreatedField = null;
        }

    }

}
</script>
