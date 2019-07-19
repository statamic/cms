<template>

    <div>
        <button
            v-if="! isRevealed"
            @click="reveal"
            class="btn"
            :disabled="isReadOnly"
            :v-tooltip="config.instructions"
            v-text="config.display" />
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
            if (! this.isReadOnly) {
                this.update(true)
            }
        }

    }

}
</script>
