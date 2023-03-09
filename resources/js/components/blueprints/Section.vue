<template>

    <div class="blueprint-section @container">
        <div class="blueprint-section-card card p-0 h-full flex rounded-t flex-col">

            <div class="bg-gray-200 border-b text-sm flex rounded-t">
                <div class="blueprint-drag-handle blueprint-section-drag-handle w-4 border-r"></div>
                <div class="p-3 py-2 flex-1">
                    <span class="font-medium mr-2">
                        <input ref="displayInput" type="text" v-model="section.display" class="bg-transparent w-full outline-none" :placeholder="`${__('Title')} (${__('Optional')})`" />
                    </span>
                    <span class="font-mono text-xs text-gray-700 mr-2"><!-- should only be shown if a prop is passed saying handle is necessary -->
                        <input type="text" v-model="section.handle" class="bg-transparent w-full outline-none" :placeholder="`${__('Handle')}`" />
                    </span>
                    <span class="text-xs text-gray-700 mr-2">
                        <input type="text" v-model="section.instructions" class="bg-transparent w-full outline-none" :placeholder="`${__('Instructions')} (${__('Optional')})`" />
                    </span>
                </div>
                <div class="flex items-center px-3">
                    <button @click.prevent="$emit('deleted')" class="flex items-center text-gray-600 hover:text-gray-950" v-if="deletable">
                        <svg-icon class="h-4 w-4" name="trash" />
                    </button>
                </div>
            </div>


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
        isSingle: { // was used for sections, but will be used for tabs in the future
            type: Boolean,
            default: false
        },
        deletable: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            editingField: null,
        }
    },

    computed: {
        suggestableConditionFields() {
            return this.section.fields.map(field => field.handle);
        }
    },

    watch: {

        section: {
            deep: true,
            handler(section) {
                this.$emit('updated', section);
            }
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

        focus() {
            if (this.$refs.displayInput) {
                this.$refs.displayInput.select();
            }
        }

    }

}
</script>
