<template>
    <div class="blueprint-section min-h-40 w-full outline-hidden @container">
        <ui-panel>
            <ui-panel-header class="flex items-center justify-between pb-0.75! pt-0! pl-2.75! pr-3.25! ">
                <div class="flex items-center gap-2 flex-1">
                    <ui-icon name="handles-sm" class="blueprint-section-drag-handle size-3! cursor-grab text-gray-400" />
                    <ui-icon :name="iconName(section.icon)" v-if="section.icon" />
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
                        :config="{ handle: 'icon', type: 'icon' }"
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
                <ui-field :label="__('Hidden')" v-if="showHideField">
                    <ui-switch v-model="editingSection.hide" />
                </ui-field>
            </div>
        </confirmation-modal>
    </div>
</template>

<script>
import Fields from './Fields.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import { Switch, Heading } from '@statamic/cms/ui';

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
        };
    },

    computed: {
        suggestableConditionFields() {
            return this.suggestableConditionFieldsProvider?.suggestableFields(this) || [];
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

        editCancelled() {
            this.editingSection = false;
        },

        iconName(name) {
            if (!name) return null;

            return name;
        },
    },
};
</script>
