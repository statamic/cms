<template>

    <stack narrow name="page-tree-linker" :before-close="shouldClose" @closed="$emit('closed')" v-slot="{ close }">
        <div class="bg-gray-100 dark:bg-dark-700 h-full flex flex-col">

            <header class="bg-white dark:bg-dark-550 rtl:pr-6 ltr:pl-6 rtl:pl-3 ltr:pr-3 py-2 mb-4 border-b dark:border-dark-950 shadow-md text-lg font-medium flex items-center justify-between">
                {{ headerText }}
                <button
                    type="button"
                    class="btn-close"
                    @click="confirmClose(close)"
                    v-html="'&times'" />
            </header>

            <div v-if="loading" class="flex-1 overflow-auto relative">
                <div class="absolute inset-0 z-10 bg-white dark:bg-dark-700 bg-opacity-75 flex items-center justify-center text-center">
                    <loading-graphic />
                </div>
            </div>

            <div v-if="!loading" class="flex-1 overflow-auto px-1">

                <publish-container
                    ref="container"
                    :name="publishContainer"
                    :blueprint="adjustedBlueprint"
                    :values="values"
                    :extra-values="extraValues"
                    :meta="meta"
                    :errors="errors"
                    :localized-fields="localizedFields"
                    :site="site"
                    class="px-2"
                    @updated="values = $event"
                    v-slot="{ container, setFieldMeta }"
                >
                    <div>
                        <div v-if="validating" class="absolute inset-0 z-10 bg-white dark:bg-dark-500 bg-opacity-75 flex items-center justify-center">
                            <loading-graphic text="" />
                        </div>

                        <publish-sections
                            :sections="adjustedBlueprint.tabs[0].sections"
                            :syncable="type == 'entry'"
                            :syncable-fields="syncableFields"
                            :read-only="readOnly"
                            @updated="setFieldValue"
                            @meta-updated="setFieldMeta"
                            @synced="syncField"
                            @desynced="desyncField"
                            @focus="container.$emit('focus', $event)"
                            @blur="container.$emit('blur', $event)"
                        />
                    </div>
                </publish-container>

            </div>

            <div v-if="!loading && (!readOnly || type === 'entry')" class="bg-gray-200 dark:bg-dark-500 p-4 border-t dark:border-dark-900 flex items-center justify-between flex-row-reverse">
                <div v-if="!readOnly">
                    <button @click="confirmClose(close)" class="btn rtl:ml-2 ltr:mr-2">{{ __('Cancel') }}</button>
                    <button @click="submit" class="btn-primary">{{ __('Submit') }}</button>
                </div>
                <div v-if="type === 'entry'">
                    <a :href="editEntryUrl" target="_blank" class="text-xs flex items-center justify-center text-blue hover:text-blue underline rtl:ml-4 ltr:mr-4">
                        <svg-icon name="light/external-link" class="w-4 h-4 rtl:ml-2 ltr:mr-2" />
                        {{ __('Edit Entry') }}
                    </a>
                </div>
            </div>

        </div>
    </stack>

</template>

<script>
import HasHiddenFields from "../publish/HasHiddenFields";

export default {

    mixins: [
        HasHiddenFields,
    ],

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

    data() {
        return {
            type: this.entry ? 'entry' : 'url',
            values: null,
            meta: null,
            originValues: null,
            originMeta: null,
            extraValues: null,
            localizedFields: null,
            syncableFields: null,
            loading: true,
            error: null,
            errors: {},
            validating: false,
            saveKeyBinding: null,
            publishContainer: 'tree-page'
        }
    },

    computed: {
        headerText() {
            return this.entry ? __('Link to Entry') : __('Nav Item');
        },

        adjustedBlueprint() {
            function getFields(blueprint) {
                return _.chain(blueprint.tabs[0].sections)
                    .map(sections => sections.fields)
                    .flatten(true)
                    .value();
            }
            function isMissingField(blueprint, handle) {
                return ! getFields(blueprint).some(field => field.handle === handle);
            }
            function hasField(blueprint, handle) {
                return ! isMissingField(blueprint, handle);
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
                const {section, field} = getField('url');
                blueprint.tabs[0].sections[section].fields.splice(field, 1);
            }

            if (isMissingField(blueprint, 'title')) {
                blueprint.tabs[0].sections[0].fields.unshift({
                    handle: 'title',
                    type: 'text',
                    display: __('Title')
                });
            }

            return blueprint;
        },
    },

    watch: {
        localizedFields(fields) {
            if (this.loading) return;

            this.$emit('localized-fields-updated', fields);
        }
    },

    methods: {
        submit() {
            this.validating = true;

            const postUrl = cp_url(`navigation/${this.handle}/pages`);

            this.$axios.post(postUrl, {
                type: this.type,
                values: this.visibleValues
            }).then(response => {
                this.$emit('submitted', this.visibleValues);
            }).catch(e => {
                this.validating = false;
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(e || 'Something went wrong');
                }
            });
        },

        shouldClose() {
            if (this.$dirty.has(this.publishContainer)) {
                if (! confirm(__('Are you sure? Unsaved changes will be lost.'))) {
                    return false;
                }
            }

            return true;
        },

        confirmClose(close) {
            if (this.shouldClose()) close();
        },

        syncField(handle) {
            if (! confirm('Are you sure? This field\'s value will be replaced by the value in the original entry.'))
                return;

            this.localizedFields = this.localizedFields.filter(field => field !== handle);
            this.$refs.container.setFieldValue(handle, this.originValues[handle]);

            // Update the meta for this field. For instance, a relationship field would have its data preloaded into it.
            // If you sync the field, the preloaded data would be outdated and an ID would show instead of the titles.
            this.meta[handle] = this.originMeta[handle];
        },

        desyncField(handle) {
            if (!this.localizedFields.includes(handle))
                this.localizedFields.push(handle);

            this.$refs.container.dirty();
        },

        setFieldValue(handle, value) {
            this.desyncField(handle);

            this.$refs.container.setFieldValue(handle, value);
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

            this.$axios.get(url).then(response => {
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
            this.syncableFields = info.syncableFields;
        },

        emitPublishInfoUpdated(isNew) {
            this.$emit('publish-info-updated', {
                values: this.values,
                originValues: this.originValues,
                meta: this.meta,
                originMeta: this.originMeta,
                extraValues: this.extraValues,
                localizedFields: this.localizedFields,
                syncableFields: this.syncableFields,
                entry: this.entry,
                new: isNew
            });
        }
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+enter', 'mod+s'], e => {
            e.preventDefault();
            this.submit();
        });

        this.getPageValues();
    },

    destroyed() {
        this.saveKeyBinding.destroy();
    }

}
</script>
