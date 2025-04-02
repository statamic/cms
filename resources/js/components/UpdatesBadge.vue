<template>
    <ui-badge v-if="count" :text="String(count)" color="red" size="sm" variant="flat" />
</template>

<script>
import { ref } from 'vue';
const count = ref(null);
const requested = ref(false);

export default {
    computed: {
        count() {
            return count.value;
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
