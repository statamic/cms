<template>
    <div>
        <modal :show="show" :saving="saving" :loading="loading" class="modal-small" :dismissible="true">
            <template slot="header">{{ translate('cp.choose_page_type') }}</template>

            <template slot="body">
                <ul class="chooser">
                    <li v-for="fieldset in fieldsets">
                        <a @click.prevent="create(fieldset.value)">{{ fieldset.text }}</a>
                    </li>
                </ul>
            </template>

            <template slot="footer">
                <div class="float-left">{{ translate('cp.parent_page') }}: <code>{{ parent }}</code></div>
                <button type="button" class="btn" @click="cancel">{{ translate('cp.cancel') }}</button>
            </template>
        </modal>
    </div>
</template>

<script>
export default {

    props: ['locale'],

    data: function() {
        return {
            parent: null,
            show: false,
            saving: false,
            loading: true,
            fieldsets: []
        }
    },

    created() {
        this.$eventHub.$on('pages.create', this.getFieldsets);
    },

    methods: {

        cancel: function() {
            this.show = false;
        },

        create(fieldset) {
            let parent = (this.parent === '/') ? '' : this.parent;

            let url = cp_url('pages/create' + parent + '?fieldset=' + fieldset);

            if (this.locale !== Object.keys(Statamic.locales)[0]) {
                url += '&locale=' + this.locale;
            }

            window.location = url;
        },

        getFieldsets(parent) {
            this.parent = parent;
            let endpoint = cp_url('fieldsets-json?url='+this.parent+'&hidden=false');
            let self = this;

            this.axios.get(endpoint).then(function(response) {
                var fieldsets = [];

                _.each(response.data.items, function(fieldset) {
                    fieldsets.push({
                        value: fieldset.uuid,
                        text: fieldset.title
                    });
                });

                // Ensure there is a default
                if (! _.findWhere(fieldsets, { value: 'default' })) {
                    fieldsets.push({ value: 'default', text: 'Default' });
                }

                // Sort alphabetically
                fieldsets = _.sortBy(fieldsets, function (fieldset) {
                    return fieldset.text;
                });

                self.fieldsets = fieldsets;
                self.loading = false;

                // If there's only one fieldset, don't make the user have to pick it.
                if (self.fieldsets.length <= 1) {
                    self.create(self.fieldsets[0].value);
                } else {
                    self.show = true;
                }
            });
        }
    }

};
</script>
