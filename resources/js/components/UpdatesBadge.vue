<template>
    <span v-if="count" class="badge-sm bg-red">
        {{ count }}
    </span>
</template>

<script>
    export default {
        data() {
            return {
                count: 0,
            };
        },

        mounted() {
            this.getCount();
        },

        created() {
            this.$events.$on('recount-updates', this.getCount);
        },

        methods: {
            getCount(clearCache = true) {
                let params = clearCache ? {'clearCache': clearCache} : {};

                this.$axios.get(cp_url('updater/count'), params).then(response => {
                    this.count = response.data;
                });
            }
        }
    }
</script>
