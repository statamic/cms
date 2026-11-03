<template>
    <ui-card class="py-0.75! px-2! field-grid-item blueprint-section-field @container" :class="[widthClass, aiRowClasses]">
        <div class="flex items-center gap-2">
            <ui-icon name="handles" class="blueprint-drag-handle size-4 shrink-0 cursor-grab text-gray-300 dark:text-gray-600" />
            <div class="flex min-w-0 flex-1 items-center justify-between gap-2">
                <div class="flex min-w-0 flex-1 items-center py-2">
                    <ui-icon
                        class="size-4 shrink-0 me-2 text-gray-500 dark:text-gray-400"
                        :name="field.icon"
                        v-tooltip="tooltipText"
                    />
                    <div class="flex min-w-0 items-center gap-2">
                        <button class="cursor-pointer truncate text-sm hover:text-ui-accent-text text-start" type="button" v-text="__(labelText)" @click="$emit('edit')" />
                        <ui-icon v-if="isReferenceField" name="link" class="shrink-0 text-gray-400 cursor-help" v-tooltip="referenceTooltipText" />
                        <span v-if="isReferenceField" class="shrink-0 text-gray-500 font-mono text-2xs cursor-help @max-2xs:hidden" v-text="__('Field')" v-tooltip="referenceTooltipText" />
                        <!-- Prototype: a field the AI chat added, until you save -->
                        <template v-if="aiChanged">
                            <ui-badge size="sm" class="shrink-0 bg-ai/5 border-ai/40 text-ai dark:border dark:bg-gray-800 dark:border-ai/60 dark:text-white" :text="__('Changed by AI')" />
                            <button type="button" class="shrink-0 cursor-pointer rounded-md px-1 py-0.5 text-2xs text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white" @click="aiUndo">
                                <ui-icon name="undo" class="inline size-3! -mt-0.5 me-0.5 align-middle" />
                                {{ __('Undo') }}
                            </button>
                        </template>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <width-selector v-if="!isHidden" v-model="width" />

                    <div
                        v-else
                        class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400  border border-gray-300 dark:border-gray-700 overflow-hidden h-6 w-14 rounded-md flex items-center justify-center"
                    >
                        <Icon name="eye-slash" class="size-4 opacity-50" />
                    </div>

                    <div class="flex items-center">
                        <ui-button v-if="canSuggest" inset size="sm" :icon="aiSuggestIcon" :variant="aiSuggestions ? 'ghost-pressed' : 'subtle'" v-tooltip="__('AI Suggestions')" @click="aiSuggestions = !aiSuggestions" />
                        <ui-button v-if="canDefineLocalizable" inset size="sm" icon="earth" :variant="localizable ? 'ghost-pressed' : 'subtle'" v-tooltip="__('Localizable')" @click="localizable = !localizable" />
                        <ui-button inset size="sm" icon="duplicate" variant="subtle" @click.prevent="$emit('duplicate')" v-tooltip="__('Duplicate')" />
                        <ui-button inset size="sm" icon="trash" variant="subtle" @click.prevent="$emit('deleted')" v-tooltip="__('Remove')" />
                    </div>

                    <Stack :open="isEditing" @update:open="editorClosed" inset :show-close-button="false" :wrap-slot="false">
                        <field-settings
                            ref="settings"
                            :id="field._id"
                            :type="field.fieldtype"
                            :root="isRoot"
                            :fields="fields"
                            :config="fieldConfig"
                            :overrides="field.config_overrides || []"
                            :suggestable-condition-fields="suggestableConditionFields"
                            :is-inside-set="isInsideSet"
                            :show-save-only-at-top-level="true"
                            @committed="settingsUpdated"
                            @closed="editorClosed"
                        />
                    </Stack>
                </div>
            </div>
        </div>
    </ui-card>
</template>

<script>
import Field from './Field.vue';
import FieldSettings from '../fields/Settings.vue';
import WidthSelector from '../fields/WidthSelector.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import { icon as aiSuggestIcon, suggestableTypes } from '../ai-suggest/use-ai-suggest.js';
import titleize from '../../util/titleize';
import deslugify from '../../util/deslugify';
import { Icon, Stack } from '@/components/ui';
import useAiChanges from '@/composables/use-ai-changes.js';

export default {
    mixins: [Field, CanDefineLocalizable],

    components: {
        FieldSettings,
        WidthSelector,
        Icon,
	    Stack,
    },

    props: ['suggestableConditionFields'],

    inject: {
        isInsideSet: { default: false },
    },

    data() {
        return {
            showHandle: false,
        };
    },

    computed: {
        // Prototype: whether the AI chat added this field (until you save or edit it), and how it's marked
        aiChanged() {
            return useAiChanges().isBlueprintChange(this.field._id);
        },

        // It opens up as it comes in, and glows in the AI colour, which settles back into the card's own shadow
        aiRowClasses() {
            return this.aiChanged ? 'ai-bp' : '';
        },

        tooltipText() {
            return this.field.fieldtype;
        },

        isReferenceField() {
            return this.field.hasOwnProperty('field_reference');
        },

        isInlineField() {
            return !this.isReferenceField;
        },

        referenceTooltipText() {
            return __('Imported from: :reference', { reference: this.field.field_reference });
        },

        fieldConfig() {
            return Object.assign({}, this.field.config, {
                handle: this.field.handle,
            });
        },

        labelText() {
            return this.field.config.display || titleize(deslugify(this.field.handle));
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
            },
        },

        isHidden() {
            return this.fieldConfig.visibility === 'hidden';
        },

        widthClass() {
            return `${field_width_class(this.width)}`;
        },

        aiSuggestIcon() {
            return aiSuggestIcon;
        },

        // Prototype: only fields that hold plain text can take a suggestion
        canSuggest() {
            return suggestableTypes.includes(this.field.fieldtype || this.field.config.type);
        },

        // Prototype: switches on the suggest button for this field, like Localizable. Left out of the config when off.
        aiSuggestions: {
            get() {
                return this.field.config.ai_suggestions || false;
            },
            set(enabled) {
                let field = this.field;
                if (enabled) field.config.ai_suggestions = true;
                else delete field.config.ai_suggestions;
                if (field.type === 'reference') field.config_overrides.push('ai_suggestions');
                this.$emit('updated', field);
            },
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
            },
        },
    },

    methods: {
        aiUndo() {
            useAiChanges().undoBlueprintField(this.field._id);
        },

        settingsUpdated(settings, editedFields) {
            // You've edited it yourself, so it's no longer the AI's
            useAiChanges().forgetBlueprintField(this.field._id);
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
        },
    },
};
</script>

<style>
/* A field the AI chat just added opens up (the same movement as the chat's cards) and glows in the AI colour. The glow settles into whatever
   shadow the card normally has, rather than fading to nothing first, so it doesn't jump when it ends. It has no end keyframe for that
   reason: the browser animates back to the card's own box-shadow. Reduced motion skips both. */
@media (prefers-reduced-motion: no-preference) {
    .ai-bp {
        interpolate-size: allow-keywords;
        animation:
            ai-bp-open 450ms cubic-bezier(0.22, 1, 0.36, 1),
            ai-bp-glow 4s ease-out;
    }
}

@keyframes ai-bp-glow {
    from {
        box-shadow:
            0 0 0 1px color-mix(in oklab, var(--color-ai) 60%, transparent),
            0 0 18px 2px color-mix(in oklab, var(--color-ai) 45%, transparent);
    }
}

@keyframes ai-bp-open {
    from {
        opacity: 0;
        height: 0;
        padding-block: 0;
        overflow: clip;
    }
}
</style>
