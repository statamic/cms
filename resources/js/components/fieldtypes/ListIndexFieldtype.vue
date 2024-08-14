<template>
    <div class="text-ellipsis">{{ formatted }}</div>
</template>

<script>
export default {

    mixins: [IndexFieldtype],

    computed: {
        formatted() {
            if (!this.value) {
                return null;
            }
            return this.value.join(', ');
        },
    },
};
</script>
