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
            :value="value" />
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
        this.$axios.get(cp_url('api/templates'), {
            params: { folder: this.config.folder },
        }).then(response => {

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

            // Filter out blank value (.gitkeep)
            templates = _.reject(templates, (template) => {
                return template === '';
            });

            // Set default
            var options = [];

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
