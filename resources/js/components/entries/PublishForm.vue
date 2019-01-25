<template>

    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a :href="collectionUrl" v-text="collectionTitle" class="text-grey hover:text-blue" />
                </small>
                {{ title }}
            </h1>

            <div
                class="mr-3 text-xs flex items-center"
                v-if="localizations.length > 1"
            >
                <button
                    v-for="loc in localizations"
                    :key="loc.handle"
                    class="inline-flex items-center py-1 px-2 rounded outline-none leading-normal"
                    :class="{ 'bg-grey-lightest': loc.active }"
                    @click="localizationSelected(loc)"
                    v-popover:tooltip.top="localizationStatusText(loc)"
                >
                    <div class="w-4 text-right flex items-center">
                        <loading-graphic :size="14" text="" class="flex -ml-1" v-if="localizing === loc.handle" />
                        <span v-if="localizing != loc.handle" class="little-dot"
                            :class="{
                                'bg-green': loc.published,
                                'bg-grey-light': !loc.published,
                                'bg-red': !loc.exists
                            }" />
                    </div>
                    {{ loc.name }}
                </button>
            </div>

            <button
                class="btn btn-primary"
                :class="{ disabled: !canSave }"
                :disabled="!canSave"
                @click.prevent="save"
                v-text="__('Save')"
            ></button>
            <slot name="action-buttons-right" />
        </div>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :fieldset="fieldset"
            :values="values"
            :meta="meta"
            :errors="errors"
            @updated="values = $event"
        >
            <publish-sections slot-scope="{ }" />
        </publish-container>
    </div>

</template>


<script>
import axios from 'axios';
import Fieldset from '../publish/Fieldset';

export default {

    props: {
        publishContainer: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialLocalizations: Array,
        collectionTitle: String,
        collectionUrl: String,
        initialAction: String,
        method: String
    },

    data() {
        return {
            action: this.initialAction,
            saving: false,
            localizing: false,
            fieldset: null,
            title: this.initialTitle,
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            localizations: _.clone(this.initialLocalizations),
            error: null,
            errors: {}
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        canSave() {
            return this.$progress.isComplete();
        }

    },

    created() {
        this.initializeFieldset(this.initialFieldset);
    },

    watch: {

        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-entry-publish-form`, saving);
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saving = true;
            this.clearErrors();

            const payload = { ...this.values, ...{
                blueprint: this.fieldset.handle
            }};

            axios[this.method](this.action, payload).then(response => {
                this.saving = false;
                this.title = response.data.title;
                this.$notify.success('Saved');
                this.$refs.container.saved();
                this.$nextTick(() => this.$emit('saved', response));
            }).catch(e => {
                this.saving = false;
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$notify.error(message, { timeout: 2000 });
                } else {
                    this.$notify.error('Something went wrong');
                }
            })
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (this.$dirty.has(this.publishContainer)) {
                if (! confirm('Are you sure? Unsaved changes will be lost.')) {
                    return;
                }
            }

            this.localizing = localization.handle;
            axios.get(localization.url).then(response => {
                const data = response.data;
                this.values = data.values;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.publishUrl = data.actions[this.action];
                this.collection = data.collection;
                this.title = data.editing ? data.values.title : this.title;
                this.action = data.actions.update;
                this.initializeFieldset(data.blueprint);
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.removeNavigationWarning());
            })
        },

        initializeFieldset(fieldset) {
            this.fieldset = new Fieldset(this.initialFieldset)
                .showSlug(true)
                .prependTitle()
                .prependMeta()
                .getFieldset();
        },

        localizationStatusText(localization) {
            if (! localization.exists) return 'This entry does not exist for this site.';

            return localization.published
                ? 'This entry exists in this site, and is published.'
                : 'This entry exists in this site, but is not published.';
        }

    }

}
</script>
