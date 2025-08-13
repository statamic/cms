<template>
    <portal name="group-fullscreen" :disabled="!fullScreenMode" :provide="provide">
        <element-container @resized="containerWidth = $event.width">
            <div :class="{ '@apply fixed inset-0 min-h-screen overflow-scroll rounded-none bg-gray-100 dark:bg-gray-900 z-998': fullScreenMode }">
                <publish-field-fullscreen-header
                    v-if="fullScreenMode"
                    :title="config.display"
                    :field-actions="fieldActions"
                    @close="toggleFullscreen"
                >
                </publish-field-fullscreen-header>
                <section :class="{ 'mt-14 p-4': fullScreenMode }">
                    <div :class="{ 'bg-white dark:bg-gray-800 dark:border-dark-900 rounded-lg border': config.border }">
                        <FieldsProvider
                            :fields="fields"
                            :field-path-prefix="fieldPathPrefix || handle"
                            :meta-path-prefix="metaPathPrefix || handle"
                        >
                            <Fields class="p-4" />
                        </FieldsProvider>
                    </div>
                </section>
            </div>
        </element-container>
    </portal>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import ManagesPreviewText from './replicator/ManagesPreviewText';
import Fields from '@statamic/components/ui/Publish/Fields.vue';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';

export default {
    mixins: [Fieldtype, ManagesPreviewText],
    components: { Fields, FieldsProvider },
    data() {
        return {
            containerWidth: null,
            isFocused: false,
            fullScreenMode: false,
            provide: {
                group: this.makeGroupProvide(),
            },
        };
    },
    computed: {
        values() {
            return this.value;
        },
        extraValues() {
            return {};
        },
        fields() {
            return this.config.fields;
        },
        previews() {
            return data_get(this.publishContainer.previews, this.fieldPathPrefix || this.handle) || {};
        },
        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return replicatorPreviewHtml(this.previewText);
        },
        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => (vm.fullScreenMode ? 'ui/shrink-all' : 'ui/expand-all'),
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
                    this.isFocused = false;
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

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },
    },
};
</script>
