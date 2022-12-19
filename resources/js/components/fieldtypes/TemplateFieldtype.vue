<template>
    <div class="template-fieldtype-container">
        <v-select
            ref="input"
            :name="name"
            @input="update"
            :clearable="config.clearable"
            :placeholder="config.placeholder"
            :disabled="isReadOnly"
            :options="options"
            :reduce="selection => selection.value"
            :searchable="true"
            :push-tags="false"
            :multiple="false"
            :value="value">
            <template #no-options>
                <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No templates to choose from.')" />
            </template>
        </v-select>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data: function() {
        return {
            loading: true,
            options: []
        }
    },

    mounted() {
        this.$axios.get(cp_url('api/templates')).then(response => {

            var templates = response.data;

            // Filter out partials
            if (this.config.hide_partials) {
                templates = _.reject(templates, (template) => {
                    return template.startsWith('partials/') || template.match(/(^_.*|\/_.*|\._.*)/g);
                });
            }

            // Filter out error templates
            templates = _.reject(templates, (template) => {
                return template.startsWith('errors/');
            });

            // Filter templates in folder
            if (this.config.folder) {
                templates = _.filter(templates, (template) => {
                    return template.startsWith(`${this.config.folder}/`);
                });
            }

            // Set default
            var options = [];

            // Prepend @blueprint as an option
            if (this.config.blueprint) {
                options.push({ label: __('Map to Blueprint'), value: '@blueprint' });
            }

            _.each(templates, (template) => {
                options.push({
                    label: this.config.folder
                        ? template.substring(this.config.folder.length + 1)
                        : template,
                    value: template
                });
            });

            this.options = options;
            this.loading = false;
        });
    }

};
</script>
