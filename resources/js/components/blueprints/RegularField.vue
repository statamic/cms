<template>

    <div class="blueprint-section-field" :class="widthClass">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex items-center flex-1 pr-2 py-1 pl-1">
                    <svg-icon class="text-grey-80 mr-1 h-4 w-4 flex-none" :name="field.icon" v-tooltip="tooltipText" default="generic-field" />
                    <a class="break-all" v-text="labelText" @click="$emit('edit')" />
                    <svg-icon name="hyperlink" v-if="isReferenceField" class="text-grey-60 text-3xs ml-1 h-4 w-4" v-tooltip="__('Imported from fieldset') + ': ' + field.field_reference" />
                </div>
                <div class="flex-none pr-1 flex">
                    <width-selector v-if="!isHidden" v-model="width" class="mr-1" />

                    <div v-else class="relative border border-grey-40 opacity-50 w-12 flex items-center justify-center mr-1">
                        <svg-icon name="hidden" class="h-4 w-4 opacity-50"></svg-icon>
                    </div>

                    <button v-if="canDefineLocalizable"
                        class="hover:text-grey-100 mr-1 flex items-center"
                        :class="{ 'text-grey-100': localizable, 'text-grey-60': !localizable }"
                        v-tooltip="__('Localizable')"
                        @click="localizable = !localizable"
                    >
                        <svg-icon name="earth" class="h-4 w-4" />
                    </button>
                    <button @click.prevent="$emit('deleted')" class="text-grey-60 hover:text-grey-100 flex items-center"><svg-icon name="trash" class="h-4 w-4" /></button>
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
            if (! this.isSectionExpanded) return 'blueprint-section-field-w-full';

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
