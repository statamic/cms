<template>
    <div>
        <template v-if="isToggleMode">
            <div class="toggle-fieldtype-wrapper">
                <toggle-input :value="isRevealed" @input="update" :read-only="isReadOnly" />
                <label v-if="config.input_label" class="font-normal ltr:ml-2 rtl:mr-2">{{
                    __(config.input_label)
                }}</label>
            </div>
        </template>

        <template v-else>
            <button
                @click="buttonReveal"
                class="btn"
                :disabled="isReadOnly"
                :v-tooltip="__(config.instructions)"
                v-text="config.input_label || __('Show Fields')"
            />
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

    inject: ['store'],

    mounted() {
        this.store.setRevealerField(this.fieldPath);
    },

    beforeUnmount() {
        this.store.unsetRevealerField(this.fieldPath);
    },

    watch: {
        fieldPath(fieldPath, oldFieldPath) {
            this.store.unsetRevealerField(oldFieldPath);
            this.$nextTick(() => {
                this.store.setRevealerField(fieldPath);
            });
        },
    },

    methods: {
        buttonReveal() {
            if (this.isReadOnly) {
                return;
            }

            this.store.setHiddenField({
                dottedKey: this.fieldPath,
                hidden: 'force',
                omitValue: true,
            });

            this.update(true);
        },
    },
};
</script>
