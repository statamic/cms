<template>
    <Badge v-if="count" :text="String(count)" :color="security ? 'red' : 'amber'" size="sm" pill />
</template>

<script>
import { ref } from 'vue';
import { Badge } from '@/components/ui';

const countRef = ref(null);
const securityRef = ref(false);
const requested = ref(false);

export default {
    components: {
        Badge,
    },

    computed: {
        count() {
            return countRef.value;
        },
        security() {
            return securityRef.value;
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
                .then((response) => {
                    countRef.value = response.data?.count ?? 0;
                    securityRef.value = response.data?.security ?? false;
                });

            requested.value = true;
        },
    },
};
</script>
