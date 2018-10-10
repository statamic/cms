<template>
    <span v-if="count" class="badge bg-red text-white rounded-full px-1">
        {{ count }}
    </span>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                count: 0,
            };
        },

        mounted() {
            this.getCount(false);
        },

        created() {
            this.$events.$on('recount-updates', this.getCount);
        },

        methods: {
            getCount(clearCache = true) {
                let params = clearCache ? {'clearCache': clearCache} : {};

                axios.get('/cp/updater/count', params).then(response => {
                    this.count = response.data;
                });
            }
        }
    }
</script>
