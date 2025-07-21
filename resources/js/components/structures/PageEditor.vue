<template>
    <stack narrow name="page-tree-linker" :before-close="shouldClose" @closed="$emit('closed')" v-slot="{ close }">
        <div class="flex h-full flex-col bg-gray-100 dark:bg-dark-700">
            <header
                class="mb-4 flex items-center justify-between border-b bg-white py-2 text-lg font-medium shadow-md dark:border-dark-950 dark:bg-dark-550 ltr:pl-6 ltr:pr-3 rtl:pl-3 rtl:pr-6"
            >
                {{ headerText }}
                <button type="button" class="btn-close" @click="confirmClose(close)" v-html="'&times'" />
            </header>

            <div v-if="loading" class="relative flex-1 overflow-auto">
                <div
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-75 text-center dark:bg-dark-700"
                >
                    <loading-graphic />
                </div>
            </div>

            <div v-if="!loading" class="flex-1 overflow-auto px-1">
                <div
                    v-if="saving"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-75 dark:bg-dark-500"
                >
                    <loading-graphic text="" />
                </div>

                <PublishContainer
                    ref="container"
                    :name="publishContainer"
                    :blueprint="adjustedBlueprint"
                    :meta="meta"
                    :errors="errors"
                    :origin-values="originValues"
                    :origin-meta="originMeta"
                    :extra-values="extraValues"
                    :site="site"
                    v-model="values"
                    v-model:modified-fields="localizedFields"
                />
            </div>

            <div
                v-if="!loading && (!readOnly || type === 'entry')"
                class="flex flex-row-reverse items-center justify-between border-t bg-gray-200 p-4 dark:border-dark-900 dark:bg-dark-500"
            >
                <div v-if="!readOnly">
                    <button @click="confirmClose(close)" class="btn ltr:mr-2 rtl:ml-2">{{ __('Cancel') }}</button>
                    <button @click="submit" class="btn-primary">{{ __('Submit') }}</button>
                </div>
                <div v-if="type === 'entry'">
                    <a
                        :href="editEntryUrl"
                        target="_blank"
                        class="flex items-center justify-center text-xs text-blue-600 underline hover:text-blue-600 ltr:mr-4 rtl:ml-4"
                    >
                        <svg-icon name="light/external-link" class="h-4 w-4 ltr:mr-2 rtl:ml-2" />
                        {{ __('Edit Entry') }}
                    </a>
                </div>
            </div>
        </div>

        <confirmation-modal
            v-if="closingWithChanges"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Discard Changes')"
            :danger="true"
            @confirm="confirmCloseWithChanges"
            @cancel="closingWithChanges = false"
        />
    </stack>
</template>

<script>
import { PublishContainer } from '@statamic/ui';
import { SavePipeline } from 'statamic';
import { flatten } from 'lodash-es';
import { computed, ref } from 'vue';
const { Pipeline, Request } = SavePipeline;

let saving = ref(false);
let errors = ref({});
let container = null;

export default {
    emits: ['closed', 'submitted', 'publish-info-updated', 'localized-fields-updated'],

    components: {
        PublishContainer,
    },

    props: {
        id: String,
        entry: String,
        site: String,
        publishInfo: Object,
        blueprint: Object,
        handle: String,
        editEntryUrl: String,
        creating: Boolean,
        readOnly: Boolean,
    },

    setup() {
        return {
            saving,
            errors,
        }
    },

    data() {
        return {
            type: this.entry ? 'entry' : 'url',
            values: null,
            meta: null,
            originValues: null,
            originMeta: null,
            extraValues: null,
            localizedFields: null,
            loading: true,
            saveKeyBinding: null,
            publishContainer: 'tree-page',
            closingWithChanges: false,
        };
    },

    computed: {
        store() {
            return this.$refs.container.store;
        },

        headerText() {
            return this.entry ? __('Link to Entry') : __('Nav Item');
        },

        adjustedBlueprint() {
            function getFields(blueprint) {
                return flatten(blueprint.tabs[0].sections.map((sections) => sections.fields));
            }
            function isMissingField(blueprint, handle) {
                return !getFields(blueprint).some((field) => field.handle === handle);
            }
            function hasField(blueprint, handle) {
                return !isMissingField(blueprint, handle);
            }
            function getField(handle) {
                for (let sectionIndex = 0; sectionIndex < blueprint.tabs[0].sections.length; sectionIndex++) {
                    const section = blueprint.tabs[0].sections[sectionIndex];
                    for (let fieldIndex = 0; fieldIndex < section.fields.length; fieldIndex++) {
                        const field = section.fields[fieldIndex];
                        if (field.handle === handle) {
                            return { section: sectionIndex, field: fieldIndex };
                        }
                    }
                }

                return { section: null, field: null };
            }

            // This UI only supports the first tab
            const blueprint = clone(this.blueprint);

            if (this.type == 'url' && isMissingField(blueprint, 'url')) {
                blueprint.tabs[0].sections[0].fields.unshift({
                    handle: 'url',
                    type: 'text',
                    display: __('URL'),
                    instructions: __('Enter any internal or external URL.'),
                });
            }

            // Remove the "url" field if it's been added to the blueprint by the user.
            // URL fields only make sense for URL type pages. Entries will have their own URLs.
            if (this.type == 'entry' && hasField(blueprint, 'url')) {
                const { section, field } = getField('url');
                blueprint.tabs[0].sections[section].fields.splice(field, 1);
            }

            if (isMissingField(blueprint, 'title')) {
                blueprint.tabs[0].sections[0].fields.unshift({
                    handle: 'title',
                    type: 'text',
                    display: __('Title'),
                });
            }

            // Make all fields localizable so they can be edited.
            // Fields are non-localizable by default, but this UI requires all fields to be editable.
            blueprint.tabs[0].sections.forEach((section, sectionIndex) => {
                section.fields.forEach((field, fieldIndex) => {
                    blueprint.tabs[0].sections[sectionIndex].fields[fieldIndex].localizable = true;
                });
            });

            return blueprint;
        },
    },

    watch: {
        localizedFields: {
            deep: true,
            handler(fields) {
                if (this.loading) return;

                this.$emit('localized-fields-updated', fields);
            }
        },
    },

    methods: {
        submit() {
            const postUrl = cp_url(`navigation/${this.handle}/pages`);
            const values = container.value.store.visibleValues;

            new Pipeline()
                .provide({ container, errors, saving })
                .through([new Request(postUrl, 'POST', {
                    type: this.type,
                    values,
                })])
                .then(() => this.$emit('submitted', values));
        },

        shouldClose() {
            if (this.$dirty.has(this.publishContainer)) {
                this.closingWithChanges = true;
                return false;
            }

            return true;
        },

        confirmClose(close) {
            if (this.shouldClose()) close();
        },

        confirmCloseWithChanges() {
            this.closingWithChanges = false;
            this.$emit('closed');
        },

        getPageValues() {
            const hasPublishInfo = !!this.publishInfo;
            const hasPublishInfoWithValues = hasPublishInfo && this.publishInfo.hasOwnProperty('values');

            if (hasPublishInfo && hasPublishInfoWithValues) {
                this.updatePublishInfo(this.publishInfo);
                this.loading = false;
                return;
            }

            const creating = this.creating || (hasPublishInfo && !hasPublishInfoWithValues);

            let url = creating
                ? cp_url(`navigation/${this.handle}/pages/create`)
                : cp_url(`navigation/${this.handle}/pages/${this.id}/edit`);

            url += `?site=${this.site}`;

            if (creating && this.type == 'entry') {
                url += `&entry=${this.entry}`;
            }

            this.$axios.get(url).then((response) => {
                this.updatePublishInfo(response.data);
                this.emitPublishInfoUpdated(hasPublishInfo && this.publishInfo.new);
                this.loading = false;
            });
        },

        updatePublishInfo(info) {
            this.values = info.values;
            this.originValues = info.originValues;
            this.meta = info.meta;
            this.originMeta = info.originMeta;
            this.extraValues = info.extraValues;
            this.localizedFields = info.localizedFields;
        },

        emitPublishInfoUpdated(isNew) {
            this.$emit('publish-info-updated', {
                values: this.values,
                originValues: this.originValues,
                meta: this.meta,
                originMeta: this.originMeta,
                extraValues: this.extraValues,
                localizedFields: this.localizedFields,
                entry: this.entry,
                new: isNew,
            });
        },
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+enter', 'mod+s'], (e) => {
            e.preventDefault();
            this.submit();
        });

        this.getPageValues();

        container = computed(() => this.$refs.container);
    },

    unmounted() {
        this.saveKeyBinding.destroy();
    },
};
</script>
