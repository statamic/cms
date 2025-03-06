<template>
    <div class="blueprint-section-field" :class="widthClass">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex flex-1 items-center py-2 ltr:pl-2 ltr:pr-4 rtl:pl-4 rtl:pr-2">
                    <svg-icon
                        class="h-4 w-4 flex-none text-gray-800 dark:text-dark-150 ltr:mr-2 rtl:ml-2"
                        :name="field.icon.startsWith('<svg') ? field.icon : `light/${field.icon}`"
                        v-tooltip="tooltipText"
                        default="light/generic-field"
                    />
                    <a class="break-all" v-text="__(labelText)" @click="$emit('edit')" />
                    <svg-icon
                        name="light/hyperlink"
                        v-if="isReferenceField"
                        class="h-4 w-4 text-3xs text-gray-600 dark:text-dark-175 ltr:ml-2 rtl:mr-2"
                        v-tooltip="__('Imported from fieldset') + ': ' + field.field_reference"
                    />
                </div>
                <div class="flex flex-none ltr:pr-2 rtl:pl-2">
                    <width-selector v-if="!isHidden" v-model="width" class="ltr:mr-2 rtl:ml-2" />

                    <div
                        v-else
                        class="relative flex w-12 items-center justify-center border border-gray-400 opacity-50 dark:border-dark-200 ltr:mr-2 rtl:ml-2"
                    >
                        <svg-icon name="regular/hidden" class="h-4 w-4 opacity-50"></svg-icon>
                    </div>

                    <button
                        v-if="canDefineLocalizable"
                        class="flex items-center hover:text-gray-950 dark:hover:text-dark-100 ltr:mr-2 rtl:ml-2"
                        :class="{
                            'text-gray-950 dark:text-dark-150': localizable,
                            'text-gray-600 dark:text-dark-200': !localizable,
                        }"
                        v-tooltip="__('Localizable')"
                        @click="localizable = !localizable"
                    >
                        <svg-icon name="light/earth" class="h-4 w-4" />
                    </button>
                    <button
                        @click.prevent="$emit('duplicate')"
                        class="flex items-center text-gray-600 hover:text-gray-950 dark:text-dark-150 dark:hover:text-dark-100 ltr:mr-2 rtl:ml-2"
                        v-tooltip="__('Duplicate')"
                    >
                        <svg-icon name="light/duplicate" class="h-4 w-4" />
                    </button>
                    <button
                        @click.prevent="$emit('deleted')"
                        class="flex items-center text-gray-600 hover:text-gray-950 dark:text-dark-150 dark:hover:text-dark-100"
                        v-tooltip="__('Remove')"
                    >
                        <svg-icon name="micro/trash" class="h-4 w-4" />
                    </button>
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
    </div>
</template>

<script>
import Field from './Field.vue';
import FieldSettings from '../fields/Settings.vue';
import WidthSelector from '../fields/WidthSelector.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import titleize from '../../util/titleize';
import deslugify from '../../util/deslugify';

export default {
    mixins: [Field, CanDefineLocalizable],

    components: {
        FieldSettings,
        WidthSelector,
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
            return `blueprint-section-field-${tailwind_width_class(this.width)}`;
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
