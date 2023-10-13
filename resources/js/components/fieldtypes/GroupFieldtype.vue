<template>
    <portal name="group-fullscreen" :disabled="!fullScreenMode" :provide="provide">

        <element-container @resized="containerWidth = $event.width">
            <div class="group-fieldtype-container" :class="{ 'grid-fullscreen bg-white': fullScreenMode }">

                <header class="relative flex items-center justify-between py-3 pl-3 bg-gray-200 border-b"
                    v-if="fullScreenMode">
                    <h2 v-text="config.display" />
                    <button class="absolute btn-close top-2 right-5" @click="fullScreenMode = false"
                        :aria-label="__('Exit Fullscreen Mode')">&times;</button>
                </header>

                <section :class="{ 'p-4': fullScreenMode }">
                    <div class="flex justify-end absolute top-3 right-3 @md:right-6" v-if="!fullScreenMode">
                        <button v-if="config.fullscreen" @click="toggleFullScreen" class="flex items-center btn btn-icon"
                            v-tooltip="__('Toggle Fullscreen Mode')">
                            <svg-icon name="expand-bold" class="h-3.5 px-0.5 text-gray-750" v-show="!fullScreenMode" />
                            <svg-icon name="shrink-all" class="h-3.5 px-0.5 text-gray-750" v-show="fullScreenMode" />
                        </button>
                    </div>

                    <div class="mb-4 border rounded shadow-sm replicator-set">
                        <div class="replicator-set-body publish-fields @container">
                            <set-field v-for="field in fields " v-show="showField(field, fieldPath(field.handle))"
                                :key="field.handle" :field="field" :meta="meta.existing[field.handle]"
                                :value="value[field.handle]" :parent-name="name" :set-index="index"
                                :errors="errors(field.handle)" :field-path="fieldPath(field.handle)" class="p-4"
                                :read-only="isReadOnly" @updated="updated(field.handle, $event)"
                                @meta-updated="metaupdated(field.handle, $event)" @focus="$emit('focus')"
                                @blur="$emit('blur')" />
                        </div>
                    </div>
                </section>

            </div>
        </element-container>

    </portal>
</template>

<script>
import SetField from './replicator/Field.vue';
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {

    mixins: [
        FieldType,
        ValidatesFieldConditions
    ],

    components: { SetField },

    data() {
        console.log(this.config);
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
        }

    },

    watch: {


    },

    methods: {

        focus() {
            // TODO
        },

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
                fieldPathPrefix: { get: () => this.fieldPathPrefix },
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

        metaUpdated(handle, value) {
            let meta = clone(this.meta.existing);
            meta[handle] = value;
            this.$emit('meta-updated', meta);
        },

        fieldPath(handle) {
            return `${this.fieldPathPrefix}.${handle}`;
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (!state) return [];
            return state.errors[this.fieldPath(handle)] || [];
        },

    }

};
</script>
