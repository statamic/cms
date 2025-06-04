<template>
    <div>
        <div class="flex items-center" v-if="isToggleMode">
            <toggle-input :model-value="isRevealed" @update:model-value="update" :read-only="isReadOnly" :id="id" />
            <label v-if="config.input_label" class="font-normal! ms-2" :for="id">
                {{ __(config.input_label) }}
            </label>
        </div>
        <Button
            v-else
            icon="eye-closed"
            @click="buttonReveal"
            :disabled="isReadOnly"
            :v-tooltip="__(config.instructions)"
            :text="config.input_label || __('Show Fields')"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Button } from '@/components/ui';
import { useId } from 'vue'

export default {
    components: {
        Button,
    },

    mixins: [Fieldtype],

    setup() {
        const id = useId();

        return { id };
    },

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
