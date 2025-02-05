<template>
    <span v-if="count" class="badge-sm bg-red-500 dark:bg-blue-900">
        {{ count }}
    </span>
</template>

<script>
export default {
    computed: {
        count() {
            return this.$store.state.updates.count;
        },
    },

    created() {
        this.registerVuexModule();

        this.getCount();
    },

    methods: {
        registerVuexModule() {
            if (this.$store.state.updates) return;

            this.$store.registerModule('updates', {
                namespaced: true,
                state: {
                    count: 0,
                    requested: false,
                },
                mutations: {
                    count: (state, count) => (state.count = count),
                    requested: (state) => (state.requested = true),
                },
            });
        },

        getCount() {
            if (this.$store.state.updates.requested) return;

            this.$axios
                .get(cp_url('updater/count'))
                .then((response) => this.$store.commit('updates/count', !isNaN(response.data) ? response.data : 0));

            this.$store.commit('updates/requested');
        },
    },
};
</script>
