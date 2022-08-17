<template>

    <div>
        <template v-if="isToggleMode">
            <div class="toggle-fieldtype-wrapper">
                <toggle-input :value="isRevealed" @input="update" :read-only="isReadOnly" />
                <label v-if="config.input_label" class="ml-1 font-normal">{{ config.input_label }}</label>
            </div>
        </template>

        <template v-else>
            <button
                @click="buttonReveal"
                class="btn"
                :disabled="isReadOnly"
                :v-tooltip="config.instructions"
                v-text="config.input_label || __('Show Fields')" />
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

        fieldPath() {
            return this.fieldPathPrefix || this.handle;
        },

    },

    inject: ['storeName'],

    mounted() {
        this.$store.commit(`publish/${this.storeName}/setRevealerField`, this.fieldPath);
    },

    watch: {
        fieldPath(fieldPath) {
            this.$store.commit(`publish/${this.storeName}/setRevealerField`, fieldPath);
        }
    },

    methods: {

        buttonReveal() {
            if (this.isReadOnly) {
                return;
            }

            this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                dottedKey: this.fieldPath,
                hidden: 'force',
                omitValue: true,
            });

            this.update(true)
        }

    }

}
</script>
