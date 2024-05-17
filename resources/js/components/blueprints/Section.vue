<template>

    <div class="blueprint-section @container">
        <div class="blueprint-section-card card p-0 h-full flex rounded-t flex-col">

            <div class="bg-gray-200 border-b text-sm flex rounded-t">
                <div class="blueprint-drag-handle blueprint-section-drag-handle w-4 ltr:border-r rtl:border-l"></div>
                <div class="p-2 flex-1 flex items-center">
                    <a class="flex items-center flex-1 group" @click="edit">
                        <svg-icon :name="iconName(section.icon)" :directory="iconBaseDirectory" class="h-4 w-4 rtl:ml-2 ltr:mr-2 text-gray-700 group-hover:text-blue-500" />
                        <div class="rtl:ml-2 ltr:mr-2" v-text="__(section.display)" />
                    </a>
                    <button class="flex items-center text-gray-700 hover:text-gray-950 rtl:ml-3 ltr:mr-3" @click="edit">
                        <svg-icon class="h-4 w-4" name="pencil" />
                    </button>
                    <button @click.prevent="$emit('deleted')" class="flex items-center text-gray-700 hover:text-gray-950">
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
                        <input type="text" class="input-text font-mono text-sm" v-model="editingSection.handle" @input="handleSyncedWithDisplay = false" />
                    </div>
                    <div class="form-group w-full">
                        <label v-text="__('Instructions')" />
                        <input type="text" class="input-text" v-model="editingSection.instructions" />
                    </div>
                    <div class="form-group w-full" v-if="showHandleField">
                        <label v-text="__('Icon')" />
                        <publish-field-meta
                            :config="{ handle: 'icon', type: 'icon', directory: this.iconBaseDirectory, folder: this.iconSubFolder }"
                            :initial-value="editingSection.icon"
                            v-slot="{ meta, value, loading }"
                        >
                            <icon-fieldtype v-if="!loading" handle="icon" :meta="meta" :value="value" @input="editingSection.icon = $event" />
                        </publish-field-meta>
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
                        class="text-2xs text-gray-600 text-center border border-dashed rounded mb-2 p-2"
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
        suggestableConditionFieldsProvider: { default: null }
    },

    components: {
        Fields,
    },

    props: {
        tabId: {
            type: String
        },
        section: {
            type: Object,
            required: true
        },
        showHandleField: {
            type: Boolean,
            default: false
        },
        editText: {
            type: String,
        }
    },

    data() {
        return {
            editingSection: false,
            editingField: null,
            handleSyncedWithDisplay: false,
        }
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
            }
        },

        'editingSection.display': function(display) {
            if (this.editingSection && this.handleSyncedWithDisplay) {
                this.editingSection.handle = this.$slugify(display, '_');
            }
        }

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
                this.$nextTick(() => this.editingField = field._id);
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
            };
        },

        editConfirmed() {
            if (! this.editingSection.handle) {
                this.editingSection.handle = this.$slugify(this.editingSection.display, '_');
            }

            this.$emit('updated', {...this.section, ...this.editingSection});
            this.editingSection = false;
        },

        editCancelled() {
            this.editingSection = false;
        },

        iconName(name) {
            if (! name) return 'folder-generic';

            return this.iconSubFolder
                ? this.iconSubFolder+'/'+name
                : name;
        },

    }

}
</script>
