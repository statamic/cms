<template>

    <div class="flex flex-col text-sm">

        <div class="blueprint-section-draggable-zone -mx-sm"
            :class="{ 'flex flex-wrap flex-1': fields.length }"
        >
            <slot name="empty-state" v-if="!fields.length" />

            <component
                v-for="(field, i) in fields"
                :is="fieldComponent(field)"
                :key="field._id"
                :field="field"
                :is-editing="editingField === field._id"
                :is-section-expanded="isSectionExpanded"
                :suggestable-condition-fields="suggestableConditionFields"
                :can-define-localizable="canDefineLocalizable"
                @edit="$emit('field-editing', field._id)"
                @updated="$emit('field-updated', i, $event)"
                @deleted="$emit('field-deleted', i)"
                @editor-closed="$emit('editor-closed')"
            />
        </div>

        <div class="blueprint-section-field-actions flex mt-1 -mx-sm">
            <div class="px-sm">
                <link-fields
                    :exclude-fieldset="excludeFieldset"
                    @linked="$emit('field-linked', $event)" />
            </div>
            <div class="px-sm">
                <button class="btn w-full flex justify-center items-center" @click="isSelectingNewFieldtype = true;">
                    <svg-icon name="wireframe" class="mr-1 w-4 h-4" />
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
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

export default {

    mixins: [CanDefineLocalizable],

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
        excludeFieldset: String,
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
                icon: field.icon,
                handle,
                config: {
                    ...field,
                    isNew: true,
                    handle
                }
            };

            this.$nextTick(() => this.pendingCreatedField = pending);
        },

        fieldCreated(created) {
            let handle = created.handle;
            delete created.handle;
            delete created.isNew;

            let field = {
                ...this.pendingCreatedField,
                ...{ handle },
                config: created
            };

            this.$emit('field-created', field);

            this.$toast.success(__('Field added'));
            this.pendingCreatedField = null;
        }

    }

}
</script>
