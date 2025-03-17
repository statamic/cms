<template>
    <span v-if="count" class="badge-sm bg-red-500 dark:bg-blue-900">
        {{ count }}
    </span>
</template>

<script>
import { ref } from 'vue';
const count = ref(null);
const requested = ref(false);

export default {
    computed: {
        count() {
            return count;
        },
    },

    created() {
        this.getCount();
    },

    methods: {
        getCount() {
            if (requested.value) return;

            this.$axios
                .get(cp_url('updater/count'))
                .then((response) => (count.value = !isNaN(response.data) ? response.data : 0));

            requested.value = true;
        },
    },
};
</script>
