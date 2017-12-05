<template>

    <div>
        <button v-if="! isRevealed" @click="reveal" class="btn" v-tip :tip-text="config.instructions">{{ config.display }}</button>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    computed: {

        isRevealed() {
            return this.data === true;
        }

    },

    methods: {

        reveal() {
            this.data = true;
        }

    }

}
</script>
