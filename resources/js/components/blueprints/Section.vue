<template>

    <div class="blueprint-section"
        :class="{
            'w-full md:w-1/2': !isEditing && !isSingle,
            'w-full': isEditing || isSingle
        }"
    >
        <div class="blueprint-section-card card p-0 h-full flex rounded-t flex-col">

            <div class="bg-grey-20 border-b text-sm flex rounded-t" v-if="!isSingle">
                <div class="blueprint-drag-handle blueprint-section-drag-handle w-4 border-r"></div>
                <div class="p-1.5 py-1 flex-1">
                    <span class="font-medium mr-1">
                        <input ref="displayInput" type="text" v-model="section.display" class="bg-transparent w-full outline-none" />
                    </span>
                    <span class="font-mono text-xs text-grey-70 mr-1">
                        <input type="text" v-model="section.handle" @input="handleSyncedWithDisplay = false" class="bg-transparent w-full outline-none" />
                    </span>
                </div>
                <div class="flex items-center px-1.5">
                    <button @click.prevent="toggleEditing" class="flex items-center text-grey-60 hover:text-grey-100 mr-1">
                        <svg-icon class="h-4 w-4" :name="isEditing ? 'shrink' : 'expand'" />
                    </button>
                    <button @click.prevent="$emit('deleted')" class="flex items-center text-grey-60 hover:text-grey-100" v-if="deletable">
                        <svg-icon class="h-4 w-4" name="trash" />
                    </button>
                </div>
            </div>


            <fields
                class="p-2"
                :fields="section.fields"
                :editing-field="editingField"
                :is-section-expanded="isEditing || isSingle"
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
                        class="text-2xs text-grey-60 text-center border border-dashed rounded mb-1 p-1"
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
        section: {
            type: Object,
            required: true
        },
        isSingle: {
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
            isEditing: false,
            editingField: null,
            handleSyncedWithDisplay: false
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
        },

        'section.display': function(display) {
            if (this.handleSyncedWithDisplay) {
                this.section.handle = this.$slugify(display, '_');
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

        focus() {
            if (this.$refs.displayInput) {
                this.$refs.displayInput.select();
            }
        },

        toggleEditing() {
            this.isEditing = ! this.isEditing
        }

    }

}
</script>
