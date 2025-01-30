<template>

    <div class="blueprint-section-field" :class="widthClass">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex items-center flex-1 rtl:pl-4 ltr:pr-4 py-2 rtl:pr-2 ltr:pl-2">
                    <svg-icon class="text-gray-800 dark:text-dark-150 rtl:ml-2 ltr:mr-2 h-4 w-4 flex-none" :name="field.icon.startsWith('<svg') ? field.icon : `light/${field.icon}`" v-tooltip="tooltipText" default="light/generic-field" />
                    <a class="break-all" v-text="__(labelText)" @click="$emit('edit')" />
                    <svg-icon name="light/hyperlink" v-if="isReferenceField" class="text-gray-600 dark:text-dark-175 text-3xs rtl:mr-2 ltr:ml-2 h-4 w-4" v-tooltip="__('Imported from fieldset') + ': ' + field.field_reference" />
                </div>
                <div class="flex-none rtl:pl-2 ltr:pr-2 flex">
                    <width-selector v-if="!isHidden" v-model="width" class="rtl:ml-2 ltr:mr-2" />

                    <div v-else class="relative border border-gray-400 dark:border-dark-200 opacity-50 w-12 flex items-center justify-center rtl:ml-2 ltr:mr-2">
                        <svg-icon name="regular/hidden" class="h-4 w-4 opacity-50"></svg-icon>
                    </div>

                    <button v-if="canDefineLocalizable"
                        class="hover:text-gray-950 dark:hover:text-dark-100 rtl:ml-2 ltr:mr-2 flex items-center"
                        :class="{ 'text-gray-950 dark:text-dark-150': localizable, 'text-gray-600 dark:text-dark-200': !localizable }"
                        v-tooltip="__('Localizable')"
                        @click="localizable = !localizable"
                    >
                        <svg-icon name="light/earth" class="h-4 w-4" />
                    </button>
                    <button @click.prevent="$emit('duplicate')" class="text-gray-600 dark:text-dark-150 hover:text-gray-950 dark:hover:text-dark-100 flex items-center rtl:ml-2 ltr:mr-2" v-tooltip="__('Duplicate')">
                        <svg-icon name="light/duplicate" class="h-4 w-4" />
                    </button>
                    <button @click.prevent="$emit('deleted')" class="text-gray-600 dark:text-dark-150 hover:text-gray-950 dark:hover:text-dark-100 flex items-center" v-tooltip="__('Remove')">
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

    props: [
        'suggestableConditionFields'
    ],

    inject: {
        isInsideSet: { default: false },
    },

     data() {
        return {
            showHandle: false,
        }
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
                handle: this.field.handle
            });
        },

        labelText() {
            return this.field.config.display
                || titleize(deslugify(this.field.handle));
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
            }
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
            }
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
        }

    }

}
</script>
