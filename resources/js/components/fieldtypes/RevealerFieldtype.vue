<template>

    <div>
        <button v-if="! isRevealed" @click="reveal" class="btn" :v-tooltip="config.instructions">{{ config.display }}</button>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    computed: {

        isRevealed() {
            return this.value;
        }

    },

    methods: {

        reveal() {
            this.update(true)
        }

    }

}
</script>
