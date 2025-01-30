<template>

    <div class="flex flex-col text-sm">

        <div class="blueprint-section-draggable-zone -mx-1"
            :class="{ 'flex flex-wrap flex-1': fields.length }"
            :data-tab="tabId"
            :data-section="sectionId"
        >
            <slot name="empty-state" v-if="!fields.length" />

            <component
                v-for="(field, i) in fields"
                :is="fieldComponent(field)"
                :key="field._id"
                :field="field"
                :fields="fields"
                :is-editing="editingField === field._id"
                :suggestable-condition-fields="suggestableConditionFields"
                :can-define-localizable="canDefineLocalizable"
                @edit="$emit('field-editing', field._id)"
                @updated="$emit('field-updated', i, $event)"
                @deleted="$emit('field-deleted', i)"
                @editor-closed="$emit('editor-closed')"
                @duplicate="duplicateField(field)"
            />
        </div>

        <div class="blueprint-section-field-actions flex mt-2 -mx-1">
            <div class="px-1">
                <link-fields
                    :exclude-fieldset="excludeFieldset"
                    @linked="$emit('field-linked', $event)" />
            </div>
            <div class="px-1">
                <button class="btn w-full flex justify-center items-center" @click="isSelectingNewFieldtype = true;">
                    <svg-icon name="light/wireframe" class="rtl:ml-2 ltr:mr-2 w-4 h-4" />
                    {{ __('Create Field') }}
                </button>
            </div>
        </div>

        <stack name="fieldtype-selector"
            v-if="isSelectingNewFieldtype"
            @closed="isSelectingNewFieldtype = false"
           v-slot="{ close }"
        >
            <fieldtype-selector @closed="close" @selected="fieldtypeSelected" />
        </stack>

        <stack name="field-settings"
            v-if="pendingCreatedField != null"
            @closed="pendingCreatedField = null"
            v-slot="{ close }"
        >
            <field-settings
                ref="settings"
                :type="pendingCreatedField.config.type"
                :root="true"
                :fields="fields"
                :config="pendingCreatedField.config"
                :suggestable-condition-fields="suggestableConditionFields"
                :is-inside-set="isInsideSet"
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
        tabId: String,
        sectionId: String,
        fields: Array,
        editingField: {},
        suggestableConditionFields: Array,
        excludeFieldset: String,
    },

    inject: {
        isInsideSet: { default: false },
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

            const pending = {
                _id: uniqid(),
                type: 'inline',
                fieldtype: field.type,
                icon: field.icon,
                config: {
                    ...field,
                    isNew: true,
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
        },

        duplicateField(field) {
            let handle = `${field.handle}_duplicate`;
            let display = field.config.display ? `${field.config.display} (Duplicate)` : `${field.handle} (Duplicate)`;

            let pending = {
                ...field,
                _id: uniqid(),
                handle: handle,
                config: {
                    ...field.config,
                    display,
                }
            };

            this.$nextTick(() => this.pendingCreatedField = pending);
        },

    }

}
</script>
