<template>
    <portal
        name="group-fullscreen"
        :disabled="!fullScreenMode"
        :provide="provide"
    >
        <element-container @resized="containerWidth = $event.width">
            <div
                class="group-fieldtype-container"
                :class="{ 'grid-fullscreen bg-white': fullScreenMode }"
            >
                <publish-field-fullscreen-header
                    v-if="fullScreenMode"
                    :title="config.display"
                    :field-actions="fieldActions"
                    @close="toggleFullscreen">
                </publish-field-fullscreen-header>
                <section :class="{ 'mt-14 p-4': fullScreenMode }">
                    <div :class="{ 'border dark:border-dark-900 rounded shadow-sm replicator-set': config.border }">
                        <div class="publish-fields @container" :class="{ 'replicator-set-body': config.border, '-mx-4': !config.border }">
                            <set-field
                                v-for="field in fields"
                                :key="field.handle"
                                v-show="showField(field, fieldPath(field.handle))"
                                :field="field"
                                :meta="meta[field.handle]"
                                :value="value[field.handle]"
                                :parent-name="name"
                                :set-index="0"
                                :errors="errors(field.handle)"
                                :field-path="fieldPath(field.handle)"
                                :read-only="isReadOnly"
                                @updated="updated(field.handle, $event)"
                                @meta-updated="updateMeta(field.handle, $event)"
                                @focus="$emit('focus')"
                                @blur="$emit('blur')"
                            />
                        </div>
                    </div>
                </section>
            </div>
        </element-container>
    </portal>
</template>

<style>
    .group-fieldtype-button-wrapper {
        @apply flex rtl:left-6 ltr:right-6 absolute top-5 sm:top-7;
    }

    .replicator-set .group-fieldtype-button-wrapper {
        @apply top-5 rtl:left-4 ltr:right-4;
    }
</style>

<script>
import Fieldtype from './Fieldtype.vue';
import SetField from './replicator/Field.vue';
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {
    mixins: [
        Fieldtype,
        ValidatesFieldConditions,
    ],
    components: { SetField },
    data() {
        return {
            containerWidth: null,
            focused: false,
            fullScreenMode: false,
            provide: {
                group: this.makeGroupProvide(),
                storeName: this.storeName,
            },
        };
    },
    inject: ['storeName'],
    computed: {
        values() {
            return this.value;
        },
        fields() {
            return this.config.fields;
        },
        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            return Object.values(this.value).join(', ');
        },
        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => vm.fullScreenMode ? 'shrink-all' : 'expand-bold',
                    quick: true,
                    run: this.toggleFullscreen,
                    visible: this.config.fullscreen,
                    visibleWhenReadOnly: true,
                },
            ];
        },
    },
    methods: {
        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        },

        toggleFullScreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },

        makeGroupProvide() {
            const group = {};
            Object.defineProperties(group, {
                config: { get: () => this.config },
                isReadOnly: { get: () => this.isReadOnly },
                handle: { get: () => this.handle },
                fieldPathPrefix: { get: () => this.fieldPathPrefix || this.handle },
                fullScreenMode: { get: () => this.fullScreenMode },
                toggleFullScreen: { get: () => this.toggleFullScreen },
            });
            return group;
        },

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                }
            }, 1);
        },

        updated(handle, value) {
            this.update({
                ...this.value,
                [handle]: value,
            });
        },

        updateMeta(handle, value) {
            this.$emit('meta-updated', { ...this.meta, [handle]: value });
        },

        fieldPath(handle) {
            return (this.fieldPathPrefix || this.handle) + '.' + handle;
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (!state) return [];
            return state.errors[this.fieldPath(handle)] || [];
        },

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },
    },
};
</script>
