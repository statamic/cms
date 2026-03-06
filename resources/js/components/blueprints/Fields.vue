<template>
    <div>
        <div
            class="blueprint-section-draggable-zone field-grid gap-2! mb-4 starting-style-transition-children"
            :data-tab="tabId"
            :data-section="sectionId"
            tabindex="-1"
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

        <div class="blueprint-section-field-actions flex gap-2">
            <LinkFields
                :exclude-fieldset="excludeFieldset"
                :with-command-palette="withCommandPalette"
                @linked="$emit('field-linked', $event)"
            />
            <ui-button icon="add-circle" :text="__('Create Field')" @click="createField" />
        </div>

        <Stack
            v-model:open="isSelectingNewFieldtype"
            @closed="isSelectingNewFieldtype = false"
            :title="__('Fieldtypes')"
            icon="cog"
            v-slot="{ close }"
        >
            <fieldtype-selector @closed="close" @selected="fieldtypeSelected" />
        </Stack>

        <Stack
            :open="pendingCreatedField != null"
            @update:open="(value) => { if (!value) pendingCreatedField = null }"
            @closed="pendingCreatedField = null"
            v-slot="{ close }"
            inset
            :show-close-button="false"
            :wrap-slot="false"
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
        </Stack>
    </div>
</template>

<script>
import { nanoid as uniqid } from 'nanoid';
import RegularField from './RegularField.vue';
import ImportField from './ImportField.vue';
import LinkFields from './LinkFields.vue';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';
import FieldSettings from '../fields/Settings.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import { Stack } from '@/components/ui';

export default {
    mixins: [CanDefineLocalizable],

    components: {
        RegularField,
        ImportField,
        LinkFields,
        FieldtypeSelector,
        FieldSettings,
	    Stack,
    },

    props: {
        tabId: String,
        sectionId: String,
        fields: Array,
        editingField: {},
        suggestableConditionFields: Array,
        excludeFieldset: String,
        withCommandPalette: Boolean,
    },

    inject: {
        isInsideSet: { default: false },
    },

    data() {
        return {
            isSelectingNewFieldtype: false,
            pendingCreatedField: null,
        };
    },

    mounted() {
        if (this.withCommandPalette) {
            this.addToCommandPalette();
        }
    },

    methods: {
        fieldComponent(field) {
            return field.type === 'import' ? 'ImportField' : 'RegularField';
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
                },
            };

            this.pendingCreatedField = pending;
        },

        createField() {
            this.isSelectingNewFieldtype = true;
        },

        fieldCreated(created) {
            let handle = created.handle;
            delete created.handle;
            delete created.isNew;

            let field = {
                ...this.pendingCreatedField,
                ...{ handle },
                config: created,
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
                },
            };

            this.$nextTick(() => (this.pendingCreatedField = pending));
        },

        addToCommandPalette() {
            if (!this.withCommandPalette) {
                return;
            }

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Create Field'),
                icon: 'add-circle',
                action: this.createField,
            });
        },
    },
};
</script>
