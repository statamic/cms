<template>
    <node-view-wrapper class="my-4">
        <div
            class="shadow-ui-sm relative z-2 w-full rounded-lg border border-gray-300 bg-white text-base dark:border-white/10 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
            :class="{
                // We’re styling a Set so that it shows a “selection outline” when selected with the mouse or keyboard.
                // The extra `&:not(:has(:focus-within))` rule turns that outline off if any element inside the Set has focus (e.g. when editing inside a Bard field).
                // This prevents the outer selection outline from showing while the user is actively working inside the Set.
                '[&:not(:has(:focus-within))]:border-blue-300! [&:not(:has(:focus-within))]:dark:border-blue-400! [&:not(:has(:focus-within))]:before:content-[\'\'] [&:not(:has(:focus-within))]:before:absolute [&:not(:has(:focus-within))]:before:inset-[-1px] [&:not(:has(:focus-within))]:before:pointer-events-none [&:not(:has(:focus-within))]:before:border-2 [&:not(:has(:focus-within))]:before:border-blue-300 [&:not(:has(:focus-within))]:dark:before:border-blue-400 [&:not(:has(:focus-within))]:before:rounded-lg': selected || withinSelection,
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
                class="group/header animate-border-color flex items-center rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 bg-gray-100/50 dark:bg-gray-925 hover:bg-gray-100 dark:hover:bg-gray-950 border-gray-300 dark:shadow-md border-b-1 border-b-transparent"
                :class="{
                    'bg-gray-200/50 dark:bg-gray-950 rounded-b-none border-b-gray-300! dark:border-b-white/10!': !collapsed
                }"
            >
                <Icon data-drag-handle name="handles" class="size-4 cursor-grab text-gray-400" v-if="!isReadOnly" />
                <button type="button" class="flex flex-1 items-center gap-4 p-2 min-w-0 cursor-pointer [&:focus-visible]:outline-none [&:focus-visible]:[&_[data-ui-badge]]:focus-outline" @click="toggleCollapsedState">
                    <Badge size="lg" :pill="true" color="white" class="px-3">
                        <span v-if="isSetGroupVisible" class="flex items-center gap-2">
                            {{ __(setGroup.display) }}
                            <Icon name="chevron-right" class="relative top-px size-3" />
                        </span>
                        {{ __(config.display) || config.handle }}
                    </Badge>
                    <Icon
                        v-if="config.instructions && !collapsed"
                        name="info-square"
                        class="size-3.5! text-gray-500"
                        v-tooltip="{ content: $markdown(__(config.instructions)), html: true }"
                    />
                    <Subheading
                        v-show="collapsed"
                        v-html="previewText"
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    />
                </button>
                <div class="flex items-center gap-2" v-if="!isReadOnly">
                    <Switch size="xs" v-model="enabled" v-tooltip="enabled ? __('Included in output') : __('Hidden from output')" />

                    <Dropdown>
                        <template #trigger>
                            <Button icon="dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
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
                class="contain-paint"
                :initial="{ height: collapsed ? '0px' : 'auto' }"
                :animate="{ height: collapsed ? '0px' : 'auto' }"
                :transition="{ duration: 0.25, type: 'tween' }"
            >
                <FieldsProvider
                    :fields="fields"
                    :as-config="false"
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
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import ManagesPreviewText from '../replicator/ManagesPreviewText';
import HasFieldActions from '../../field-actions/HasFieldActions.js';
import { Motion } from 'motion-v';
import {
    Badge,
    Button,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownSeparator,
    Icon,
    Subheading,
    Switch,
    PublishFieldsProvider as FieldsProvider,
    PublishFields as Fields
} from '@ui';
import { containerContextKey } from '@/components/ui/Publish/containerContext.js';
import { watch } from 'vue';

export default {
    props: nodeViewProps,

    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        Fields,
        FieldsProvider,
        Switch,
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

    mounted() {
        watch(
            () => data_get(this.publishContainer.values.value, this.fieldPathPrefix),
            (values) => {
                this.updateAttributes({ values });
            },
            { deep: true }
        );
    },

    updated() {
        // This is a workaround to avoid Firefox's inability to select inputs/textareas when the
        // parent element is set to draggable: https://bugzilla.mozilla.org/show_bug.cgi?id=739071
        this.$el.setAttribute('draggable', false);
    },
};
</script>
