<template>
    <div class="theme-fieldtype-wrapper">
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ __('Loading') }}
        </div>
        <select-fieldtype v-if="!loading" :name="name" :data.sync="data" :config="selectConfig"></select-fieldtype>
    </div>
</template>

<script>
export default {

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            loading: true,
            options: {}
        }
    },

    computed: {
        selectConfig: function() {
            return {
                options: this.options
            };
        }
    },

    mounted() {
        this.$http.get(cp_url('system/themes/get'), function(data) {
            var options = [];
            _.each(data, function(theme) {
                options.push({
                    value: theme.folder,
                    text: theme.name
                });
            });
            this.options = options;
            this.loading = false;
        });
    }

};
</script>
