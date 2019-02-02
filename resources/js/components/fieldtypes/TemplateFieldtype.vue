<template>
    <div>
        <loading-graphic :inline="true" v-if="loading" />
        <select-input v-if="!loading" :name="name" :value="value" @input="update" :options="options" />
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

        this.axios.get(cp_url('api/templates')).then(response => {
            var options = [];

            _.each(response.data, function(template) {
                options.push({
                    value: template,
                    text: template
                });
            });

            this.options = options;
            this.loading = false;
        });

    }

};
</script>
