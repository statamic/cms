<template>
    <div class="blueprint-section @container">
        <div class="blueprint-section-card card flex h-full flex-col rounded-t p-0 dark:bg-dark-800">
            <div class="flex rounded-t border-b bg-gray-200 text-sm dark:border-none dark:bg-dark-600">
                <div
                    class="blueprint-drag-handle blueprint-section-drag-handle w-4 dark:border-dark-900 ltr:border-r rtl:border-l"
                ></div>
                <div class="flex flex-1 items-center p-2">
                    <a class="group flex flex-1 items-center" @click="edit">
                        <svg-icon
                            :name="iconName(section.icon)"
                            :directory="iconBaseDirectory"
                            class="h-4 w-4 text-gray-700 group-hover:text-blue-500 dark:text-dark-150 dark:group-hover:text-dark-blue-100 ltr:mr-2 rtl:ml-2"
                        />
                        <div class="ltr:mr-2 rtl:ml-2" v-text="__(section.display)" />
                    </a>
                    <button
                        class="flex items-center text-gray-700 hover:text-gray-950 dark:text-dark-175 dark:hover:text-dark-100 ltr:mr-3 rtl:ml-3"
                        @click="edit"
                    >
                        <svg-icon class="h-4 w-4" name="pencil" />
                    </button>
                    <button
                        @click.prevent="$emit('deleted')"
                        class="flex items-center text-gray-700 hover:text-gray-950 dark:text-dark-175 dark:hover:text-dark-100"
                    >
                        <svg-icon class="h-4 w-4" name="micro/trash" />
                    </button>
                </div>
            </div>

            <confirmation-modal
                v-if="editingSection"
                :title="editText"
                @opened="$refs.displayInput.select()"
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

            <fields
                class="p-4"
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
                    <div
                        v-text="__('Add or drag fields here')"
                        class="mb-2 rounded-sm border border-dashed p-2 text-center text-2xs text-gray-600 dark:border-dark-200 dark:text-dark-150"
                    />
                </template>
            </fields>
        </div>
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
