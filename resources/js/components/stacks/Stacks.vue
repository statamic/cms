<template>
    <div class="stacks-on-stacks">
        <div class="stack-hit-area" @click="clicked" :style="{ zIndex: 1 }" />
        <portal-target
            v-for="(stack, i) in stacks"
            :key="`stack-${stack}-${i}`"
            :name="`stack-${i}`"
        />
    </div>
</template>

<script>
export default {

    computed: {

        stacks() {
            return this.$root.stacks;
        }

    },

    created() {
        this.$events.$on('stacks.0.hit-area-clicked', () => {
            console.log(`bottom hit area clicked. all stacks will close`);
        });
    },

    methods: {

        clicked() {
            console.log('clicked bottom hit area');
        }

    }

}
</script>

