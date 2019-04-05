<template>

    <div class="blueprint-section-field" :class="widthClass">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex-1 px-2 py-1 pl-1">
                    <span v-if="isReferenceField" class="icon icon-link text-grey-40 mr-1"></span>
                    <span v-if="isInlineField" class="icon icon-layers text-grey-40 mr-1"></span>
                    <span class="font-medium mr-1">{{ field.config.display || field.handle }}</span>
                    <span class="font-mono text-2xs text-grey-40">{{ field.handle }}</span>
                </div>
                <div class="pr-1 flex">
                    <width-selector v-model="width" class="mr-1" v-show="isSectionExpanded" />
                    <button @click.prevent="$emit('edit')" class="opacity-50 hover:opacity-100"><span class="icon icon-cog" /></button>
                    <button @click.prevent="$emit('deleted')" class="opacity-50 hover:opacity-100"><span class="icon icon-cross" /></button>

                    <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
                        <field-settings
                            ref="settings"
                            :type="field.config.type"
                            :root="isRoot"
                            :config="fieldConfig"
                            @updated="configFieldUpdated"
                            @input="configUpdated"
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

export default {

    mixins: [Field],

    components: {
        FieldSettings,
        WidthSelector,
    },

    computed: {

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

        width: {
            get() {
                return this.field.config.width;
            },
            set(width) {
                this.configFieldUpdated('width', width);
            }
        },

        widthClass() {
            if (! this.isSectionExpanded) return 'blueprint-section-field-w-full';

            return `blueprint-section-field-${tailwind_width_class(this.width)}`;
        }
    },

    methods: {

        configFieldUpdated(handle, value) {
            if (handle === 'handle') {
                Vue.set(this.field, handle, value);
            } else {
                Vue.set(this.field.config, handle, value);

                if (this.field.type === 'reference' && this.field.config_overrides.indexOf(handle) === -1) {
                    this.field.config_overrides.push(handle);
                }
            }

            this.$emit('updated', this.field);
        },

        configUpdated(config) {
            delete config.handle;
            this.field.config = config;

            this.$emit('updated', this.field);
        },

        editorClosed() {
            this.$emit('editor-closed');
        }

    }

}
</script>
