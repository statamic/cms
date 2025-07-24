<template>
    <div class="blueprint-section min-h-40 w-full outline-hidden @container">
        <ui-panel>
            <ui-panel-header class="flex items-center justify-between pb-0.75! pt-0! pl-2.75! pr-3.25! ">
                <div class="flex items-center gap-2 flex-1">
                    <ui-icon name="handles-sm" class="blueprint-section-drag-handle size-3! cursor-grab text-gray-400" />
                    <!-- @TODO: Add backwards support for old icons -->
                    <!-- <svg-icon :name="iconName(section.icon)" :directory="iconBaseDirectory" /> -->
                    <ui-heading v-text="__(section.display ?? 'Section')" />
                </div>
                <ui-button icon="pencil-line" size="sm" variant="ghost" @click="edit" />
                <ui-button icon="trash" size="sm" variant="ghost" @click.prevent="$emit('deleted')" />
            </ui-panel-header>

            <Fields
                :tab-id="tabId"
                :section-id="section._id"
                :fields="section.fields"
                :editing-field="editingField"
                :suggestable-condition-fields="suggestableConditionFields"
                :can-define-localizable="canDefineLocalizable"
                @field-created="fieldCreated"
                @field-updated="fieldUpdated"
                @field-deleted="deleteField"
                @field-linked="fieldLinked"
                @field-editing="editingField = $event"
                @editor-closed="editingField = null"
            >
                <template v-slot:empty-state>
                    <ui-subheading
                        v-text="__('Drag and drop fields below.')"
                        class="rounded-xl min-h-16 flex items-center justify-center border border-dashed border-gray-300 p-3 text-center w-full"
                    />
                </template>
            </Fields>
        </ui-panel>

        <confirmation-modal
            v-if="editingSection"
            :title="editText"
            @opened="$refs.displayInput?.select()"
            @confirm="editConfirmed"
            @cancel="editCancelled"
        >
            <div class="publish-fields">
                <div class="form-group w-full">
                    <label v-text="__('Display')" />
                    <input ref="displayInput" type="text" class="input-text" v-model="editingSection.display" />
                </div>
                <div class="form-group w-full" v-if="showHandleField">
                    <label v-text="__('Handle')" />
                    <input
                        type="text"
                        class="input-text font-mono text-sm"
                        v-model="editingSection.handle"
                        @input="handleSyncedWithDisplay = false"
                    />
                </div>
                <div class="form-group w-full">
                    <label v-text="__('Instructions')" />
                    <input type="text" class="input-text" v-model="editingSection.instructions" />
                </div>
                <div class="form-group w-full">
                    <publish-field-meta
                        :config="{
                            handle: 'collapsible',
                            type: 'toggle',
                            default: false,
                            inline_label: 'Collapsible',
                        }"
                        :initial-value="editingSection.collapsible"
                        v-slot="{ meta, value, config }"
                    >
                        <toggle-fieldtype
                            handle="collapsible"
                            :config="config"
                            :meta="meta"
                            :value="value"
                            @update:value="editingSection.collapsible = $event"
                        />
                    </publish-field-meta>
                </div> 
                <div class="form-group w-full" v-if="editingSection.collapsible">
                    <publish-field-meta
                        :config="{
                            handle: 'collapsed_by_default',
                            type: 'toggle',
                            default: false,
                            inline_label: 'Collapsed by Default',
                        }"
                        :initial-value="editingSection.collapsed_by_default"
                        v-slot="{ meta, value, config }"
                    >
                        <toggle-fieldtype
                            handle="collapsed_by_default"
                            :config="config"
                            :meta="meta"
                            :value="value"
                            @update:value="editingSection.collapsed_by_default = $event"
                        />
                    </publish-field-meta>
                </div>
                <div class="form-group w-full" v-if="showHandleField">
                    <label v-text="__('Icon')" />
                    <publish-field-meta
                        :config="{
                            handle: 'icon',
                            type: 'icon',
                            directory: this.iconBaseDirectory,
                            folder: this.iconSubFolder,
                        }"
                        :initial-value="editingSection.icon"
                        v-slot="{ meta, value, loading, config }"
                    >
                        <icon-fieldtype
                            v-if="!loading"
                            handle="icon"
                            :config="config"
                            :meta="meta"
                            :value="value"
                            @input="editingSection.icon = $event"
                        />
                    </publish-field-meta>
                </div>
                <div class="form-group w-full" v-if="showHideField">
                    <label v-text="__('Hidden')" />
                    <toggle-input v-model="editingSection.hide" />
                </div>
            </div>
        </confirmation-modal>
    </div>
</template>

<script>
import Fields from './Fields.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

export default {
    mixins: [CanDefineLocalizable],

    inject: {
        suggestableConditionFieldsProvider: { default: null },
    },

    components: {
        Fields,
    },

    props: {
        tabId: {
            type: String,
        },
        section: {
            type: Object,
            required: true,
        },
        showHandleField: {
            type: Boolean,
            default: false,
        },
        showHideField: {
            type: Boolean,
            default: false,
        },
        editText: {
            type: String,
        },
    },

    data() {
        return {
            editingSection: false,
            editingField: null,
            handleSyncedWithDisplay: false,
        };
    },

    computed: {
        suggestableConditionFields() {
            return this.suggestableConditionFieldsProvider?.suggestableFields(this) || [];
        },

        iconBaseDirectory() {
            return this.$config.get('setIconsDirectory');
        },

        iconSubFolder() {
            return this.$config.get('setIconsFolder');
        },
    },

    watch: {
        section: {
            deep: true,
            handler(section) {
                this.$emit('updated', section);
            },
        },

        'editingSection.display': function (display) {
            if (this.editingSection && this.handleSyncedWithDisplay) {
                this.editingSection.handle = snake_case(display);
            }
        },
    },

    created() {
        // This logic isn't ideal, but it was better than passing along a 'isNew' boolean and having
        // to deal with stripping it out and making it not new, etc. Good enough for a quick win.
        if (!this.section.handle || this.section.handle == 'new_section' || this.section.handle == 'new_set') {
            this.handleSyncedWithDisplay = true;
        }
    },

    methods: {
        fieldLinked(field) {
            this.section.fields.push(field);
            this.$toast.success(__('Field added'));

            if (field.type === 'reference') {
                this.$nextTick(() => (this.editingField = field._id));
            }
        },

        fieldCreated(field) {
            this.section.fields.push(field);
        },

        fieldUpdated(i, field) {
            this.section.fields.splice(i, 1, field);
        },

        deleteField(i) {
            this.section.fields.splice(i, 1);
        },

        edit() {
            this.editingSection = {
                display: this.section.display,
                handle: this.section.handle,
                instructions: this.section.instructions,
                icon: this.section.icon,
                hide: this.section.hide,
                collapsible: this.section.collapsible,
                collapsed_by_default: this.section.collapsed_by_default,
            };
        },

        editConfirmed() {
            if (!this.editingSection.handle) {
                this.editingSection.handle = snake_case(this.editingSection.display);
            }

            this.$emit('updated', { ...this.section, ...this.editingSection });
            this.editingSection = false;
        },

        editCancelled() {
            this.editingSection = false;
        },

        iconName(name) {
            if (!name) return 'folder-generic';

            return this.iconSubFolder ? this.iconSubFolder + '/' + name : name;
        },
    },
};
</script>
