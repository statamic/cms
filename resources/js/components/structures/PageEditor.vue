<template>

    <stack narrow name="page-tree-linker" :before-close="shouldClose" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ headerText }}
                <button
                    type="button"
                    class="btn-close"
                    @click="confirmClose(close)"
                    v-html="'&times'" />
            </div>

            <div v-if="loading" class="flex-1 overflow-auto relative">
                <div class="absolute inset-0 z-10 bg-white bg-opacity-75 flex items-center justify-center text-center">
                    <loading-graphic />
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">

                <publish-container
                    ref="container"
                    :name="publishContainer"
                    :blueprint="adjustedBlueprint"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    :localized-fields="localizedFields"
                    @updated="values = $event"
                >
                    <div slot-scope="{ container, setFieldMeta }">
                        <div v-if="validating" class="absolute inset-0 z-10 bg-white bg-opacity-75 flex items-center justify-center">
                            <loading-graphic text="" />
                        </div>

                        <publish-fields
                            :fields="fields"
                            :syncable="type == 'entry'"
                            :syncable-fields="syncableFields"
                            @updated="setFieldValue"
                            @meta-updated="setFieldMeta"
                            @synced="syncField"
                            @desynced="desyncField"
                            @focus="container.$emit('focus', $event)"
                            @blur="container.$emit('blur', $event)"
                        />
                    </div>
                </publish-container>

                <div class="p-3">
                    <button @click="submit" class="btn-primary w-full">{{ __('Submit') }}</button>

                    <div class="text-xs mt-2" v-if="type === 'entry'">
                        <a :href="editEntryUrl" target="_blank" class="flex items-center justify-center text-blue hover:text-blue-dark underline">
                            <svg-icon name="external-link" class="w-4 h-4 mr-1" />
                            {{ __('Edit Entry') }}
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </stack>

</template>

<script>
export default {

    props: {
        id: String,
        entry: String,
        site: String,
        publishInfo: Object,
        blueprint: Object,
        handle: String,
        editEntryUrl: String,
        creating: Boolean
    },

    data() {
        return {
            type: this.entry ? 'entry' : 'url',
            values: null,
            meta: null,
            originValues: null,
            originMeta: null,
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
            function isMissingField(fields, handle) {
                return ! fields.some(field => field.handle === handle);
            }
            function hasField(fields, handle) {
                return ! isMissingField(fields, handle);
            }

            // This UI only supports the first section
            const blueprint = clone(this.blueprint);
            const fields = blueprint.sections[0].fields;

            if (this.type == 'url' && isMissingField(fields, 'url')) {
                fields.unshift({
                    handle: 'url',
                    type: 'text',
                    display: __('URL'),
                    instructions: __('Enter any internal or external URL.'),
                });
            }

            // Remove the "url" field if it's been added to the blueprint by the user.
            // URL fields only make sense for URL type pages. Entries will have their own URLs.
            if (this.type == 'entry' && hasField(fields, 'url')) {
                fields.splice(fields.indexOf(fields.find(field => field.handle === 'url')), 1);
            }

            if (isMissingField(fields, 'title')) {
                fields.unshift({
                    handle: 'title',
                    type: 'text',
                    display: __('Title')
                });
            }

            return { ...blueprint, sections: [{ fields }] };
        },

        fields() {
            return _.chain(this.adjustedBlueprint.sections)
                .map(section => section.fields)
                .flatten(true)
                .value();
        }
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
                values: this.values
            }).then(response => {
                this.$emit('submitted', this.values);
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
            this.localizedFields = info.localizedFields;
            this.syncableFields = info.syncableFields;
        },

        emitPublishInfoUpdated(isNew) {
            this.$emit('publish-info-updated', {
                values: this.values,
                originValues: this.originValues,
                meta: this.meta,
                originMeta: this.originMeta,
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
