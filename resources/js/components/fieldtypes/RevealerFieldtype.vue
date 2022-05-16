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

    inject: ['storeName'],

    mounted() {
        this.$store.commit(`publish/${this.storeName}/setRevealerFields`, this.fieldPathPrefix || this.handle);
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
