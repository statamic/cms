<template>
    <ui-card class="py-0.75! px-2! field-grid-item blueprint-section-field" :class="widthClass">
        <div class="flex items-center gap-2">
            <ui-icon name="handles" class="blueprint-drag-handle size-4 cursor-grab text-gray-300" />
            <div class="flex flex-1 items-center justify-between">
                <div class="flex flex-1 items-center py-2">
                    <ui-icon
                        class="size-4 me-2 text-gray-500"
                        :name="field.icon.startsWith('<svg') ? field.icon : `fieldtype-${field.icon}`"
                        v-tooltip="tooltipText"
                    />
                    <div class="flex items-center gap-2">
                        <button class="cursor-pointer overflow-hidden text-ellipsis text-sm hover:text-blue-500" type="button" v-text="__(labelText)" @click="$emit('edit')" />
                        <ui-icon v-if="isReferenceField" name="link" class="text-gray-400" />
                        <span v-if="isReferenceField" class="text-gray-500 font-mono text-2xs cursor-help" v-text="__('field')" v-tooltip="__('Imported from: ') + field.field_reference" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <width-selector v-if="!isHidden" v-model="width" />

                    <div
                        v-else
                        class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400  border border-gray-300 dark:border-gray-700 overflow-hidden h-6 w-14 rounded-md flex items-center justify-center"
                    >
                        <Icon name="eye-slash" class="size-4 opacity-50" />
                    </div>

                    <div class="flex items-center">
                        <ui-button v-if="canDefineLocalizable" inset size="sm" icon="earth" :variant="localizable ? 'ghost' : 'subtle'" v-tooltip="__('Localizable')" @click="localizable = !localizable" />
                        <ui-button inset size="sm" icon="duplicate" variant="subtle" @click.prevent="$emit('duplicate')" v-tooltip="__('Duplicate')" />
                        <ui-button inset size="sm" icon="trash" variant="subtle" @click.prevent="$emit('deleted')" v-tooltip="__('Remove')" />
                    </div>

                    <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
                        <field-settings
                            ref="settings"
                            :id="field._id"
                            :type="field.fieldtype"
                            :root="isRoot"
                            :fields="fields"
                            :config="fieldConfig"
                            :overrides="field.config_overrides || []"
                            :suggestable-condition-fields="suggestableConditionFields"
                            :is-inside-set="isInsideSet"
                            @committed="settingsUpdated"
                            @closed="editorClosed"
                        />
                    </stack>
                </div>
            </div>
        </div>
    </ui-card>
</template>

<script>
import Field from './Field.vue';
import FieldSettings from '../fields/Settings.vue';
import WidthSelector from '../fields/WidthSelector.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import titleize from '../../util/titleize';
import deslugify from '../../util/deslugify';
import { Icon } from '@statamic/ui';

export default {
    mixins: [Field, CanDefineLocalizable],

    components: {
        FieldSettings,
        WidthSelector,
        Icon,
    },

    props: ['suggestableConditionFields'],

    inject: {
        isInsideSet: { default: false },
    },

    data() {
        return {
            showHandle: false,
        };
    },

    computed: {
        tooltipText() {
            return this.field.fieldtype;
        },

        isReferenceField() {
            return this.field.hasOwnProperty('field_reference');
        },

        isInlineField() {
            return !this.isReferenceField;
        },

        fieldConfig() {
            return Object.assign({}, this.field.config, {
                handle: this.field.handle,
            });
        },

        labelText() {
            return this.field.config.display || titleize(deslugify(this.field.handle));
        },

        width: {
            get() {
                return this.field.config.width;
            },
            set(width) {
                let field = this.field;
                field.config.width = width;
                if (field.type === 'reference') field.config_overrides.push('width');
                this.$emit('updated', field);
            },
        },

        isHidden() {
            return this.fieldConfig.visibility === 'hidden';
        },

        widthClass() {
            return `${field_width_class(this.width)}`;
        },

        localizable: {
            get() {
                return this.field.config.localizable || false;
            },
            set(localizable) {
                let field = this.field;
                field.config.localizable = localizable;
                if (field.type === 'reference') field.config_overrides.push('localizable');
                this.$emit('updated', field);
            },
        },
    },

    methods: {
        settingsUpdated(settings, editedFields) {
            let field = this.field;

            // Handle is stored separately from the config.
            field.handle = settings.handle;
            delete settings.handle;

            field.config = settings;

            if (field.type === 'reference') {
                field.config_overrides = editedFields;
            }

            this.$emit('updated', field);
        },

        editorClosed() {
            this.$emit('editor-closed');
        },
    },
};
</script>
