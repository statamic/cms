<template>

    <div class="blueprint-section-field" :class="widthClass">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex items-center flex-1 pr-4 py-2 pl-2">
                    <svg-icon class="text-gray-800 mr-2 h-4 w-4 flex-none" :name="field.icon.startsWith('<svg') ? field.icon : `light/${field.icon}`" v-tooltip="tooltipText" default="light/generic-field" />
                    <a class="break-all" v-text="labelText" @click="$emit('edit')" />
                    <svg-icon name="light/hyperlink" v-if="isReferenceField" class="text-gray-600 text-3xs ml-2 h-4 w-4" v-tooltip="__('Imported from fieldset') + ': ' + field.field_reference" />
                </div>
                <div class="flex-none pr-2 flex">
                    <width-selector v-if="!isHidden" v-model="width" class="mr-2" />

                    <div v-else class="relative border border-gray-400 opacity-50 w-12 flex items-center justify-center mr-2">
                        <svg-icon name="regular/hidden" class="h-4 w-4 opacity-50"></svg-icon>
                    </div>

                    <button v-if="canDefineLocalizable"
                        class="hover:text-gray-950 mr-2 flex items-center"
                        :class="{ 'text-gray-950': localizable, 'text-gray-600': !localizable }"
                        v-tooltip="__('Localizable')"
                        @click="localizable = !localizable"
                    >
                        <svg-icon name="light/earth" class="h-4 w-4" />
                    </button>
                    <button @click.prevent="$emit('duplicate')" class="text-gray-600 hover:text-gray-950 flex items-center mr-2" v-tooltip="__('Duplicate')">
                        <svg-icon name="light/duplicate" class="h-4 w-4" />
                    </button>
                    <button @click.prevent="$emit('deleted')" class="text-gray-600 hover:text-gray-950 flex items-center" v-tooltip="__('Remove')">
                        <svg-icon name="micro/trash" class="h-4 w-4" />
                    </button>
                    <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
                        <field-settings
                            ref="settings"
                            :type="field.fieldtype"
                            :root="isRoot"
                            :config="fieldConfig"
                            :overrides="field.config_overrides || []"
                            :suggestable-condition-fields="suggestableConditionFields"
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

export default {

    mixins: [Field, CanDefineLocalizable],

    components: {
        FieldSettings,
        WidthSelector,
    },

    props: [
        'suggestableConditionFields'
    ],

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
                || Vue.options.filters.titleize(Vue.options.filters.deslugify(this.field.handle));
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
