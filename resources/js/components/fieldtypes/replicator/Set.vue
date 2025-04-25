<template>
    <div :class="sortableItemClass">
        <slot name="picker" />
        <div layout class="w-full bg-white relative z-2 dark:bg-gray-900 border border-gray-200 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm text-base rounded-lg"
            :data-collapsed="collapsed ?? undefined"
            :data-error="this.hasError ?? undefined"
            :data-invalid="isInvalid ?? undefined"
            :data-readonly="isReadOnly ?? undefined"
            :data-type="config.handle"
        >
            <header class="flex items-center px-1.5 antialiased group/header hover:bg-gray-50 border-b border-transparent animate-border-color duration-200 rounded-lg" :class="{ 'border-gray-200! rounded-b-none dark:border-white/15': !collapsed }">
                <Icon name="handles" class="item-move cursor-grab sortable-handle size-4 text-gray-400" v-if="!isReadOnly" />
                <button type="button" class="p-2 flex flex-1 items-center gap-4" @click="toggleCollapsedState">
                    <ui-badge variant="flat" size="lg">
                        <span v-if="isSetGroupVisible">
                            {{ __(setGroup.display) }}
                            <Icon name="ui/chevron-right" class="size-3 relative top-px" />
                        </span>
                        {{ display || config.handle }}
                    </ui-badge>
                    <Icon
                        v-if="config.instructions && !collapsed"
                        name="info-square"
                        class="size-3.5! text-gray-500"
                        v-tooltip="{ content: $markdown(__(config.instructions)), html: true }"
                    />
                    <ui-subheading
                        v-show="collapsed"
                        v-html="previewText"
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    />
                </button>
                <div class="flex items-center gap-2" v-if="!isReadOnly">
                    <ui-tooltip :text="values.enabled ? __('Included in output') : __('Hidden from output')">
                        <Switch
                            size="xs"
                            @update:model-value="toggleEnabledState"
                            :model-value="values.enabled"
                        />
                    </ui-tooltip>

                    <!-- @TODO: Replace with UI/Dropdown when Actions are more isolatable -->
                    <dropdown-list>
                        <dropdown-actions :actions="fieldActions" v-if="fieldActions.length" />
                        <div class="divider" />
                        <dropdown-item
                            :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))"
                            @click="toggleCollapsedState"
                        />
                        <dropdown-item :text="__('Duplicate Set')" @click="duplicate" v-if="canAddSet" />
                        <dropdown-item :text="__('Delete Set')" class="warning" @click="destroy" />
                    </dropdown-list>
                </div>
            </header>

            <Motion
                layout
                class="publish-fields overflow-hidden @container"
                :initial="{ height: collapsed ? '0px' : 'auto' }"
                :animate="{ height: collapsed ? '0px' : 'auto' }"
                :transition="{ duration: .25, type: 'tween' }"
            >
                <set-field
                    v-for="field in fields"
                    v-show="showField(field, fieldPath(field))"
                    :key="field.handle"
                    :field="field"
                    :meta="meta[field.handle]"
                    :value="values[field.handle]"
                    :parent-name="parentName"
                    :set-index="index"
                    :field-path="fieldPath(field)"
                    :read-only="isReadOnly"
                    :show-field-previews="showFieldPreviews"
                    @updated="updated(field.handle, $event)"
                    @meta-updated="metaUpdated(field.handle, $event)"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                    @replicator-preview-updated="previewUpdated(field.handle, $event)"
                />
            </Motion>
        </div>
    </div>
</template>

<script>
import SetField from './Field.vue';
import ManagesPreviewText from './ManagesPreviewText';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';
import HasFieldActions from '../../field-actions/HasFieldActions.js';
import DropdownActions from '../../field-actions/DropdownActions.vue';
import { Icon, Switch } from '@statamic/ui';
import { Motion } from 'motion-v';

export default {
    components: { SetField, DropdownActions, Icon, Switch, Motion },

    mixins: [ValidatesFieldConditions, ManagesPreviewText, HasFieldActions],

    inject: ['replicatorSets', 'store', 'storeName'],

    props: {
        config: { type: Object, required: true },
        meta: { type: Object, required: true },
        index: { type: Number, required: true },
        collapsed: { type: Boolean, default: false },
        values: { type: Object, required: true },
        parentName: { type: String, required: true },
        fieldPathPrefix: { type: String, required: true },
        hasError: { type: Boolean, default: false },
        sortableItemClass: { type: String },
        sortableHandleClass: { type: String },
        canAddSet: { type: Boolean, default: true },
        isReadOnly: { type: Boolean, default: false },
        previews: { type: Object },
        showFieldPreviews: { type: Boolean, default: true },
    },

    data() {
        return {
            fieldPreviews: this.previews,
            extraValues: {},
        };
    },

    computed: {
        fields() {
            return this.config.fields;
        },

        display() {
            return __(this.config.display) || this.values.type;
        },

        instructions() {
            return this.config.instructions ? markdown(__(this.config.instructions)) : null;
        },

        setGroup() {
            if (this.replicatorSets.length < 1) return null;

            return this.replicatorSets.find((group) => {
                return group.sets.filter((set) => set.handle === this.config.handle).length > 0;
            });
        },

        hasMultipleFields() {
            return this.fields.length > 1;
        },

        isSetGroupVisible() {
            return this.replicatorSets.length > 1 && this.setGroup?.display;
        },

        isHidden() {
            return this.values['#hidden'] === true;
        },

        isInvalid() {
            return Object.keys(this.config).length === 0;
        },

        fieldVm() {
            let vm = this;
            while (vm !== vm.$root) {
                if (vm.$options.name === 'replicator-fieldtype') return vm;
                vm = vm.$parent;
            }
        },

        fieldActionPayload() {
            return {
                vm: this,
                fieldVm: this.fieldVm,
                fieldPathPrefix: this.fieldPathPrefix,
                index: this.index,
                values: this.values,
                config: this.config,
                meta: this.meta,
                update: (handle, value) => this.updated(handle, value),
                updateMeta: (handle, value) => this.metaUpdated(handle, value),
                isReadOnly: this.isReadOnly,
                store: this.store,
                storeName: this.storeName,
            };
        },
    },

    methods: {
        updated(handle, value) {
            this.$emit('updated', this.index, { ...this.values, [handle]: value });
        },

        metaUpdated(handle, value) {
            this.$emit('meta-updated', { ...this.meta, [handle]: value });
        },

        previewUpdated(handle, value) {
            this.$emit('previews-updated', (this.fieldPreviews = { ...this.fieldPreviews, [handle]: value }));
        },

        destroy() {
            if (!confirm(__('Are you sure?'))) return;

            this.$emit('removed');
        },

        toggle() {
            this.isHidden ? this.expand() : this.collapse();
        },

        toggleEnabledState() {
            this.updated('enabled', !this.values.enabled);
        },

        toggleCollapsedState() {
            if (this.collapsed) {
                this.expand();
            } else {
                this.collapse();
            }
        },

        collapse() {
            this.$emit('collapsed');
        },

        expand() {
            this.$emit('expanded');
        },

        duplicate() {
            this.$emit('duplicated');
        },

        fieldPath(field) {
            return `${this.fieldPathPrefix}.${this.index}.${field.handle}`;
        },
    },
};
</script>
