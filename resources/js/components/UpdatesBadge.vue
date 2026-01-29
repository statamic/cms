<template>
    <Badge v-if="count" :text="String(count)" color="red" size="sm" pill />
</template>

<script>
import { ref } from 'vue';
import { Badge } from '@/components/ui';

const count = ref(null);
const requested = ref(false);

export default {
    components: {
        Badge,
    },

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
