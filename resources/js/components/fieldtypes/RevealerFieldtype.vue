<template>

    <div>
        <template v-if="isToggleMode">
            <div class="toggle-fieldtype-wrapper">
                <toggle-input :value="isRevealed" @input="update" :read-only="isReadOnly" />
                <label v-if="config.input_label" class="rtl:mr-2 ltr:ml-2 font-normal">{{ __(config.input_label) }}</label>
            </div>
        </template>

        <template v-else>
            <button
                @click="buttonReveal"
                class="btn"
                :disabled="isReadOnly"
                :v-tooltip="__(config.instructions)"
                v-text="config.input_label || __('Show Fields')" />
        </template>
    </div>

</template>

<script>
import Fieldtype from './Fieldtype.vue';

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

    beforeDestroy() {
        this.$store.commit(`publish/${this.storeName}/unsetRevealerField`, this.fieldPath);
    },

    watch: {
        fieldPath(fieldPath, oldFieldPath) {
            this.$store.commit(`publish/${this.storeName}/unsetRevealerField`, oldFieldPath);
            this.$nextTick(() => {
                this.$store.commit(`publish/${this.storeName}/setRevealerField`, fieldPath);
            });
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
