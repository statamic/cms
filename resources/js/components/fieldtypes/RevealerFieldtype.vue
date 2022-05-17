<template>

    <div>
        <template v-if="isToggleMode">
            <label class="block pb-1">{{ config.display }}</label>
            <div class="toggle-fieldtype-wrapper">
                <toggle-input :value="isRevealed" @input="update" :read-only="isReadOnly" />
                <label v-if="config.inline_label" class="ml-1 font-normal">{{ config.inline_label }}</label>
            </div>
        </template>

        <template v-else>
            <button
                v-if="! isRevealed"
                @click="reveal"
                class="btn"
                :disabled="isReadOnly"
                :v-tooltip="config.instructions"
                v-text="config.display" />
        </template>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    computed: {

        isRevealed() {
            return this.value;
        },

        isToggleMode() {
            return data_get(this.config, 'mode') === 'toggle';
        },

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
