<template>
    <div class="blueprint-section min-h-40 w-full outline-hidden @container">
        <ui-panel>
            <ui-panel-header class="flex items-center justify-between pl-2.75! pr-3.25!">
                <div class="flex items-center gap-2 flex-1">
                    <ui-icon name="handles-sm" class="blueprint-section-drag-handle size-3! cursor-grab text-gray-400" />
                    <ui-icon :name="section.icon" :set="iconSet" v-if="section.icon" />
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
                        v-text="__('Add or drag fields here.')"
                        class="rounded-xl min-h-16 flex items-center justify-center border border-dashed border-gray-300 dark:border-gray-700 p-3 text-center w-full"
                    />
                </template>
            </Fields>
        </ui-panel>

        <ui-stack
            narrow
            v-if="editingSection"
            @opened="() => $nextTick(() => $refs.displayInput.focus())"
            @closed="editCancelled"
        >
            <div class="h-full overflow-scroll overflow-x-auto bg-white px-6 dark:bg-dark-800">
                <header class="py-2 -mx-6 px-6 border-b border-gray-200 dark:border-gray-700 mb-5">
                    <div class="flex items-center justify-between">
                        <ui-heading size="lg">
                            {{ editText }}
                        </ui-heading>
                        <ui-button icon="x" variant="ghost" class="-me-2" @click="editCancelled" />
                    </div>
                </header>
                <div class="space-y-6">
                    <ui-field :label="__('Display')">
                        <ui-input ref="displayInput" type="text" v-model="editingSection.display" />
                    </ui-field>
                    <ui-field :label="__('Handle')" v-if="showHandleField">
                        <ui-input
                            type="text"
                            class="font-mono text-sm"
                            v-model="editingSection.handle"
                            @input="handleSyncedWithDisplay = false"
                        />
                    </ui-field>
                    <ui-field :label="__('Instructions')">
                        <ui-input type="text" v-model="editingSection.instructions" />
                    </ui-field>
                    <ui-field :label="__('Collapsible')">
                        <ui-switch v-model="editingSection.collapsible" />
                    </ui-field>
                    <ui-field :label="__('Collapsed by default')" v-if="editingSection.collapsible">
                        <ui-switch v-model="editingSection.collapsed" />
                    </ui-field>
                    <ui-field :label="__('Icon')" v-if="showHandleField">
                        <publish-field-meta
                            :config="{
                                handle: 'icon',
                                type: 'icon',
                                set: iconSet,
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
                                @update:value="editingSection.icon = $event"
                            />
                        </publish-field-meta>
                    </ui-field>
                    <ui-field :label="__('Preview Image')" v-if="showHandleField && previewImageContainer">
                        <publish-field-meta
                            :config="{
                                handle: 'image',
                                type: 'assets',
                                container: previewImageContainer,
                                folder: previewImageFolder,
                                restrict: !! previewImageFolder,
                                allow_uploads: true,
                                show_set_alt: false,
                                max_items: 1
                            }"
                            :initial-value="editingSection.image"
                            v-slot="{ meta, value, loading, config }"
                        >
                            <assets-fieldtype
                                v-if="!loading"
                                handle="image"
                                :config="config"
                                :meta="meta"
                                :value="value"
                                @update:value="editingSection.image = $event?.[0] || null"
                            />
                        </publish-field-meta>
                    </ui-field>
                    <ui-field :label="__('Hidden')" v-if="showHideField">
                        <ui-switch v-model="editingSection.hide" />
                    </ui-field>
                    <div class="py-6 space-x-2 -mx-6 px-6 border-t border-gray-200 dark:border-gray-700">
                        <ui-button :text="isSoloNarrowStack ? __('Save') : __('Confirm')" @click="handleSaveOrConfirm" variant="primary" />
                        <ui-button :text="__('Cancel')" @click="editCancelled" variant="ghost" />
                    </div>
                </div>
            </div>
        </ui-stack>
    </div>
</template>

<script>
import Fields from './Fields.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import { Switch, Heading } from '@/components/ui';

export default {
    mixins: [CanDefineLocalizable],

    inject: {
        suggestableConditionFieldsProvider: { default: null },
    },

    components: {
        Fields,
        Switch,
        Heading,
    },

    props: {
        tabId: { type: String },
        section: { type: Object, required: true },
        showHandleField: { type: Boolean, default: false },
        showHideField: { type: Boolean, default: false },
        editText: { type: String },
    },

    data() {
        return {
            editingSection: false,
            editingField: null,
            handleSyncedWithDisplay: false,
            saveKeyBinding: null,
        };
    },

    computed: {
        suggestableConditionFields() {
            return this.suggestableConditionFieldsProvider?.suggestableFields(this) || [];
        },

        iconSet() {
            return this.$config.get('replicatorSetIcons') || undefined;
        },

        previewImageContainer() {
            return this.$config.get('setPreviewImages.container') || null;
        },

        previewImageFolder() {
            return this.$config.get('setPreviewImages.folder') || null;
        },

        isSoloNarrowStack() {
            const stacks = this.$stacks.stacks();
            return stacks.length === 1 && stacks[0]?.data?.vm?.narrow === true;
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

        editingSection: {
            handler(isEditing) {
                if (isEditing) {
                    // Bind Cmd+S to trigger save or confirm based on stack type
                    this.saveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.handleSaveOrConfirm();
                    });
                } else {
                    // Unbind when stack is closed
                    if (this.saveKeyBinding) {
                        this.saveKeyBinding.destroy();
                        this.saveKeyBinding = null;
                    }
                }
            },
            immediate: false,
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
                image: this.section.image,
                hide: this.section.hide,
                collapsible: this.section.collapsible,
                collapsed: this.section.collapsed,
            };
        },

        editConfirmed() {
            if (!this.editingSection.handle) {
                this.editingSection.handle = snake_case(this.editingSection.display);
            }

            this.$emit('updated', { ...this.section, ...this.editingSection });
            this.editingSection = false;
        },

        handleSaveOrConfirm() {
            if (this.isSoloNarrowStack) {
                this.editAndSave();
            } else {
                this.editConfirmed();
            }
        },

        editAndSave() {
            this.editConfirmed();
            this.$nextTick(() => {
                this.$events.$emit('root-form-save');
            });
        },

        editCancelled() {
            this.editingSection = false;
        },
    },

    beforeUnmount() {
        if (this.saveKeyBinding) {
            this.saveKeyBinding.destroy();
        }
    },
};
</script>
