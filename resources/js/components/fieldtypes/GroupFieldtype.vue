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
                <header
                    v-if="fullScreenMode"
                    class="relative flex items-center justify-between py-3 pl-3 bg-gray-200 border-b"
                >
                    <h2 v-text="config.display" />
                    <button
                        @click="fullScreenMode = false"
                        class="absolute btn-close top-2 right-5"
                        :aria-label="__('Exit Fullscreen Mode')"
                    >&times;</button>
                </header>
                <section :class="{ 'p-4': fullScreenMode }">
                    <div
                        v-if="!fullScreenMode"
                        class="flex justify-end absolute top-3 right-3 @md:right-6"
                    >
                        <button
                            v-if="config.fullscreen"
                            @click="toggleFullScreen"
                            v-tooltip="__('Toggle Fullscreen Mode')"
                            class="flex items-center btn btn-icon"
                        >
                            <svg-icon
                                v-show="!fullScreenMode"
                                name="expand-bold"
                                class="h-3.5 px-0.5 text-gray-750"
                            />
                            <svg-icon
                                v-show="fullScreenMode"
                                name="shrink-all"
                                class="h-3.5 px-0.5 text-gray-750"
                            />
                        </button>
                    </div>
                    <div class="mb-4 border rounded shadow-sm replicator-set">
                        <div class="replicator-set-body publish-fields @container">
                            <set-field
                                v-for="field in fields"
                                :key="field.handle"
                                v-show="showField(field, fieldPath(field.handle))"
                                :field="field"
                                :meta="meta[field.handle]"
                                :value="value[field.handle]"
                                :parent-name="name"
                                :set-index="index"
                                :errors="errors(field.handle)"
                                :field-path="fieldPath(field.handle)"
                                :read-only="isReadOnly"
                                @updated="updated(field.handle, $event)"
                                @meta-updated="updateMeta(field.handle, $event)"
                                @focus="$emit('focus')"
                                @blur="$emit('blur')"
                                class="p-4"
                            />
                        </div>
                    </div>
                </section>
            </div>
        </element-container>
    </portal>
</template>
<script>
import FieldType from './Fieldtype.vue';
import SetField from './replicator/Field.vue';
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {
    mixins: [
        FieldType,
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
        fields() {
            return this.config.fields;
        },
        replicatorPreview() {
            return Object.values(this.value).join(', ');
        }
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
            return `${this.fieldPathPrefix}.${handle}`;
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (!state) return [];
            return state.errors[this.fieldPath(handle)] || [];
        },
    },
};
</script>
