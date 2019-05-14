<template>
    <div class="user-roles-fieldtype-container">
        <v-select
            ref="input"
            :name="name"
            @input="update"
            :clearable="config.clearable"
            :disabled="config.disabled"
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
        this.$axios.get(cp_url('roles')).then(response => {
            var options = [];

            _.each(response.data, function(role) {
                options.push({
                    value: role.handle,
                    label: role.title
                });
            });

            this.options = options;
            this.loading = false;
        });
    },

    methods: {
        handleUpdate(value) {
            this.update(value.value)
        }
    }

};
</script>
