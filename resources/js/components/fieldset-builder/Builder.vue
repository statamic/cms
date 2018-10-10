<template>

<div>

    <div class="flex items-center mb-3 sticky">
        <h1 class="flex-1" v-if="create">
            {{ translate('cp.create_fieldset') }}
        </h1>

        <h1 class="flex-1" v-else>
            {{ translate('cp.editing_fieldset') }}:
            <strong>{{ fieldsetTitle }}</strong>
        </h1>
        <button type="button" class="btn btn-primary" v-on:click="save()" :disabled="!canSave">{{ translate('cp.save') }}</button>
    </div>

    <div class="px-3">
        <div class="alert alert-danger" v-if="hasErrors">
            <ul>
                <li v-for="error in errors">{{ error }}</li>
            </ul>
        </div>

        <div class="card p-0">
            <div v-if="loading && !errorMessage" class="loading">
                <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
            </div>

            <div v-if="errorMessage" class="alert alert-danger">
                <p>{{ errorMessage }}</p>
            </div>

            <div class="fieldset-builder flex flex-wrap" v-if="! loading">

                <div class="form-group p-2 mb-0 w-full flex items-center border-b">
                    <div class="w-2/3 pr-2">
                        <label class="block">{{ translate('cp.title') }}</label>
                        <small class="help-block mb-0">{{ translate('cp.fieldset_title_instructions') }}</small>
                    </div>
                    <div class="w-1/3 flex justify-end">
                        <input type="text" class="form-control" v-model="fieldset.title" autofocus="autofocus" />
                    </div>
                </div>

                <div class="form-group p-2 mb-0 w-full flex items-center border-b" v-if="create">
                    <div class="w-2/3 pr-2">
                        <label class="block">{{ translate('cp.handle') }}</label>
                        <small class="help-block mb-0">{{ translate('cp.fieldset_handle_instructions') }}</small>
                    </div>
                    <div class="w-1/3 flex justify-end">
                        <input type="text" class="form-control" v-model="slug" />
                    </div>
                </div>

                <div class="form-group p-2 mb-0 w-full flex items-center border-b">
                    <div class="w-2/3 pr-2">
                        <label class="block">{{ translate('cp.create_title') }}</label>
                        <small class="help-block mb-0">{{ translate('cp.fieldset_create_title_instructions') }}</small>
                    </div>
                    <div class="w-1/3 flex justify-end">
                        <input type="text" class="form-control" v-model="fieldset.create_title" />
                    </div>
                </div>

                <div class="form-group p-2 mb-0 w-full flex items-center border-b">
                    <div class="w-2/3 pr-2">
                        <label class="block">{{ translate('cp.hide') }}</label>
                        <small class="help-block mb-0">{{ translate('cp.fieldset_hide_instructions') }}</small>
                    </div>
                    <div class="w-1/3 flex justify-end">
                        <toggle-fieldtype :data.sync="fieldset.hide"></toggle-fieldtype>
                    </div>
                </div>

                <div class="form-group p-2 mb-0 w-full flex items-center">
                    <div class="w-2/3 pr-2">
                        <label class="block">{{ translate('cp.append_taxonomies') }}</label>
                        <small class="help-block mb-0">{{ translate('cp.append_taxonomies_instructions') }}</small>
                    </div>
                    <div class="w-1/3 flex justify-end">
                        <toggle-fieldtype :data.sync="fieldset.taxonomies"></toggle-fieldtype>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center little-heading mx-1 opacity-50">{{ translate('cp.sections_and_fields') }}</div>

        <section-builder
            v-if="!loading"
            ref="section-builder"
            :fieldtypes="fieldtypes"
            :sections.sync="fieldset.sections">
        </section-builder>

    </div>
</div>

</template>


<script>
Mousetrap = require('mousetrap');

export default {

    components: {
        SectionBuilder: require('./Sections/SectionBuilder.vue')
    },

    props: {
        'fieldsetTitle': String,
        'create': {
            type: Boolean,
            default: false
        },
        'saveUrl': String
    },

    data: function () {
        return {
            loading: true,
            saving: false,
            errors: [],
            errorMessage: null,
            slug: null,
            fieldset: { title: '', sections: null },
            fieldtypes: []
        }
    },

    computed: {
        canSave() {
            return this.fieldset.title !== '';
        },

        hasErrors: function() {
            return _.size(this.errors) !== 0;
        },
    },

    methods: {
        getFieldtypes: function() {
            var self = this;
            this.$http.get(cp_url('/fieldtypes')).success(function(data) {
                _.each(data, function(fieldtype) {
                    self.fieldtypes.push(fieldtype);
                });

                self.getFieldset();
            });
        },

        getFieldset: function() {
            var self = this;

            var url = cp_url(`/fieldsets-json/${get_from_segment(3)}/edit`);

            self.$http.get(url).success(function (data) {
                this.fieldset = data;
                self.loading = false;

                // Add the watcher on the next tick after the request is complete. This prevents any changes being
                // triggered by this component or any child components that modify the fieldset when they are initialized.
                this.$nextTick(() => {
                    this.$watch('fieldset', () => {
                        this.$dispatch('changesMade', true);
                    }, { deep: true });
                });
            }).error(function (data) {
                self.errorMessage = data.message;
            });
        },

        save() {
            this.saving = true;
            this.errors = [];

            this.$http.post(this.saveUrl, {
                slug: this.slug,
                fieldset: this.fieldset
            }).success(data => {
                if (data.success) {
                    this.$dispatch('changesMade', false);
                    if (this.create) {
                        window.location = data.redirect;
                        return;
                    }
                    this.saving = false;
                    this.$dispatch('setFlashSuccess', data.message, { timeout: 1500 });
                } else {
                    this.$dispatch('setFlashError', translate('cp.error'));
                    this.saving = false;
                    this.errors = data.errors;
                    $('html, body').animate({ scrollTop: 0 });
                }
            }).error(data => {
                alert('There was a problem saving the fieldset. Please check your logs.');
            });
        },

        fieldDeleted(field) {
            this.$refs.sectionBuilder.deleteField(field);
        }
    },

    mounted() {
        this.getFieldtypes();

        Mousetrap.bindGlobal('mod+s', (e) => {
            e.preventDefault();

            this.save();
        });
    }
};
</script>
