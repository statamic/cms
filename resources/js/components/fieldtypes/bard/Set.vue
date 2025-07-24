<template>
    <node-view-wrapper>
        <Motion
            layout
            class="flex justify-center py-3 relative group"
            :initial="{ paddingTop: '0.75rem', paddingBottom: '0.75rem' }"
            :hover="{ paddingTop: '1.25rem', paddingBottom: '1.25rem' }"
            :transition="{ duration: 0.2 }"
        >
            <div v-if="showConnector" class="absolute group-hover:opacity-0 transition-opacity delay-25 duration-125 inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-dark-700" />
            <button class="w-full absolute inset-0 h-full opacity-0 group-hover:opacity-100 transition-opacity delay-25 duration-75 cursor-pointer">
                <div class="h-full flex flex-col justify-center">
                    <div class="rounded-full bg-gray-200 h-2" />
                </div>
            </button>
            <Button v-if="enabled" round icon="plus" size="sm" class="-my-4 z-3 opacity-0 group-hover:opacity-100 transition-opacity delay-25 duration-75" />
        </Motion>
        <div
            class="shadow-ui-sm relative z-2 w-full rounded-lg border border-gray-200 bg-white text-base dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
            :class="{
                'dark:border-dark-blue-100 border-blue-400!': selected || withinSelection,
                'border-red-500': hasError,
            }"
            :data-type="config.handle"
            contenteditable="false"
            @copy.stop
            @paste.stop
            @cut.stop
        >
            <div ref="content" hidden />
            <header
                class="group/header animate-border-color flex items-center rounded-lg border-b border-transparent px-1.5 antialiased duration-200 hover:bg-gray-50"
                :class="{ 'rounded-b-none border-gray-200! dark:border-white/10': !collapsed, invalid: isInvalid }"
            >
                <Icon data-drag-handle name="handles" class="size-4 cursor-grab text-gray-400" v-if="!isReadOnly" />
                <button type="button" class="flex flex-1 items-center gap-4 p-2" @click="toggleCollapsedState">
                    <Badge variant="flat" size="lg">
                        <span v-if="isSetGroupVisible">
                            {{ __(setGroup.display) }}
                            <Icon name="ui/chevron-right" class="relative top-px size-3" />
                        </span>
                        {{ __(config.display) || config.handle }}
                    </Badge>
                    <Tooltip :markdown="__(config.instructions)">
                        <Icon
                            v-if="config.instructions && !collapsed"
                            name="info-square"
                            class="size-3.5! text-gray-500"
                        />
                    </Tooltip>
                    <Subheading
                        v-show="collapsed"
                        v-html="previewText"
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    />
                </button>
                <div class="flex items-center gap-2" v-if="!isReadOnly">
                    <Tooltip :text="enabled ? __('Included in output') : __('Hidden from output')">
                        <Switch size="xs" v-model="enabled" />
                    </Tooltip>

                    <Dropdown>
                        <template #trigger>
                            <Button icon="ui/dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
                        </template>
                        <DropdownMenu>
                            <DropdownItem
                                v-if="fieldActions.length"
                                v-for="action in fieldActions"
                                :text="action.title"
                                :variant="action.dangerous ? 'destructive' : 'default'"
                                @click="action.run(action)"
                            />
                            <DropdownSeparator v-if="fieldActions.length" />
                            <DropdownItem
                                :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))"
                                @click="toggleCollapsedState"
                            />
                            <DropdownItem :text="__('Duplicate Set')" @click="duplicate" />
                            <DropdownItem
                                :text="__('Delete Set')"
                                variant="destructive"
                                @click="deleteNode"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </div>
            </header>

            <Motion
                layout
                v-if="index !== undefined"
                class="overflow-hidden"
                :initial="{ height: collapsed ? '0px' : 'auto' }"
                :animate="{ height: collapsed ? '0px' : 'auto' }"
                :transition="{ duration: 0.25, type: 'tween' }"
            >
                <FieldsProvider
                    :fields="fields"
                    :field-path-prefix="fieldPathPrefix"
                    :meta-path-prefix="metaPathPrefix"
                >
                    <Fields class="p-4" />
                </FieldsProvider>
            </Motion>
        </div>
    </node-view-wrapper>
</template>

<script>
import { NodeViewWrapper } from '@tiptap/vue-3';
import ManagesPreviewText from '../replicator/ManagesPreviewText';
import HasFieldActions from '../../field-actions/HasFieldActions.js';
import { Badge, Button, Dropdown, DropdownMenu, DropdownItem, DropdownSeparator, Icon, Subheading, Switch, Tooltip } from '@statamic/ui';
import { Motion } from 'motion-v';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import Fields from '@statamic/components/ui/Publish/Fields.vue';
import { within } from '@popperjs/core/lib/utils/within.js';
import { containerContextKey } from '@statamic/components/ui/Publish/Container.vue';

export default {
    props: {
        editor: { type: Object, required: true },
        node: { type: Object, required: true },
        decorations: { type: Array, required: true },
        selected: { type: Boolean, required: true },
        extension: { type: Object, required: true },
        getPos: { type: Function, required: true },
        updateAttributes: { type: Function, required: true },
        deleteNode: { type: Function, required: true },
        showConnector: { type: Boolean, default: true },
    },

    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        Fields,
        FieldsProvider,
        Switch,
        Tooltip,
        Subheading,
        Badge,
        Icon,
        NodeViewWrapper,
        Motion,
    },

    mixins: [ManagesPreviewText, HasFieldActions],

    inject: {
        bard: {},
        bardSets: {},
        publishContainer: { from: containerContextKey },
    },

    computed: {
        fields() {
            return this.config.fields;
        },

        display() {
            return __(this.config.display || this.values.type);
        },

        values() {
            return this.node.attrs.values;
        },

        extraValues() {
            return {};
        },

        meta() {
            return this.extension.options.bard.meta.existing[this.node.attrs.id] || {};
        },

        previews() {
            return data_get(this.publishContainer.previews.value, this.fieldPathPrefix) || {};
        },

        collapsed() {
            return this.extension.options.bard.meta.collapsed.includes(this.node.attrs.id);
        },

        config() {
            return this.setConfigs.find((c) => c.handle === this.values.type) || {};
        },

        setConfigs() {
            return this.bard.setConfigs;
        },

        setGroup() {
            if (this.bardSets.length < 1) return null;

            return this.bardSets.find((group) => {
                return group.sets.filter((set) => set.handle === this.config.handle).length > 0;
            });
        },

        isSetGroupVisible() {
            return this.bardSets.length > 1 && this.setGroup?.display;
        },

        isReadOnly() {
            return this.bard.isReadOnly;
        },

        enabled: {
            get() {
                return this.node.attrs.enabled;
            },
            set(enabled) {
                return this.updateAttributes({ enabled });
            },
        },

        parentName() {
            return this.extension.options.bard.name;
        },

        index() {
            return this.extension.options.bard.setIndexes[this.node.attrs.id];
        },

        fieldPathPrefix() {
            const fpf = this.extension.options.bard.fieldPathPrefix;
            const handle = this.extension.options.bard.handle;
            const prefix = fpf ? `${fpf}.${handle}` : handle;

            return `${prefix}.${this.index}.attrs.values`;
        },

        metaPathPrefix() {
            const mpp = this.extension.options.bard.metaPathPrefix;
            const handle = this.extension.options.bard.handle;
            const prefix = mpp ? `${mpp}.${handle}` : handle;

            return `${prefix}.existing.${this.node.attrs.id}`;
        },

        instructions() {
            return this.config.instructions ? markdown(__(this.config.instructions)) : null;
        },

        hasError() {
            return this.extension.options.bard.setHasError(this.node.attrs.id);
        },

        showFieldPreviews() {
            return this.extension.options.bard.config.previews;
        },

        isInvalid() {
            return Object.keys(this.config).length === 0;
        },

        decorationSpecs() {
            return Object.assign({}, ...this.decorations.map((decoration) => decoration.type.spec));
        },

        withinSelection() {
            return this.decorationSpecs.withinSelection;
        },

        fieldVm() {
            return this.extension.options.bard;
        },

        fieldActionPayload() {
            return {
                // vm: this,
                // fieldVm: this.fieldVm,
                // fieldPathPrefix: this.fieldVm.fieldPathPrefix || this.fieldVm.handle,
                index: this.index,
                values: this.values,
                config: this.config,
                // meta: this.meta,
                update: (handle, value) =>
                    this.publishContainer.setFieldValue(`${this.fieldPathPrefix}.${handle}`, value),
                updateMeta: (handle, value) =>
                    this.publishContainer.setFieldMeta(`${this.metaPathPrefix}.${handle}`, value),
                isReadOnly: this.isReadOnly,
            };
        },

        fieldActionBinding() {
            return 'bard-fieldtype-set';
        }
    },

    methods: {
        within() {
            return within;
        },
        focused() {
            this.extension.options.bard.$emit('focus');
        },

        blurred() {
            // Bard should only blur if we focus somewhere outside of Bard entirely.
            // We use a timeout because activeElement only exists after the blur event.
            setTimeout(() => {
                const bard = this.extension.options.bard;
                if (!bard.$el.contains(document.activeElement)) bard.$emit('blur');
            }, 1);
        },

        toggleCollapsedState() {
            if (this.collapsed) {
                this.expand();
            } else {
                this.collapse();
            }
        },

        collapse() {
            // this.$events.$emit('collapsed', this.node.attrs.id);
            this.extension.options.bard.collapseSet(this.node.attrs.id);
        },

        expand() {
            // this.$events.$emit('expanded', this.node.attrs.id);
            this.extension.options.bard.expandSet(this.node.attrs.id);
        },

        duplicate() {
            // this.$events.$emit('duplicated', this.node.attrs.id);
            this.extension.options.bard.duplicateSet(
                this.node.attrs.id,
                this.node.attrs,
                this.getPos() + this.node.nodeSize,
            );
        },
    },

    updated() {
        // This is a workaround to avoid Firefox's inability to select inputs/textareas when the
        // parent element is set to draggable: https://bugzilla.mozilla.org/show_bug.cgi?id=739071
        this.$el.setAttribute('draggable', false);
    },
};
</script>
