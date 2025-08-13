<template>
    <ButtonGroup ref="buttonGroup">
        <Button
            v-for="(option, $index) in options"
            ref="button"
            :disabled="config.disabled"
            :key="$index"
            :name="name"
            :read-only="isReadOnly"
            :text="option.label || option.value"
            :value="option.value"
            :variant="value == option.value ? 'primary' : 'default'"
            @click="updateSelectedOption(option.value)"
        />
    </ButtonGroup>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import ResizeObserver from 'resize-observer-polyfill';
import { Button, ButtonGroup } from '@statamic/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],
    components: {
        Button,
        ButtonGroup
    },

    data() {
        return {
            resizeObserver: null,
        };
    },

    mounted() {
        this.setupResizeObserver();
    },

    beforeUnmount() {
        this.resizeObserver.disconnect();
    },

    computed: {
        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            var option = this.options.find((o) => o.value === this.value);
            return option ? option.label : this.value;
        },
    },

    methods: {
        updateSelectedOption(newValue) {
            this.update(this.value == newValue && this.config.clearable ? null : newValue);
        },

        setupResizeObserver() {
            this.resizeObserver = new ResizeObserver(() => {
                this.handleWrappingOfNode(this.$refs.buttonGroup.$el);
            });
            this.resizeObserver.observe(this.$refs.buttonGroup.$el);
        },

        handleWrappingOfNode(node) {
            const lastEl = node.lastChild;

            if (!lastEl) return;

            node.classList.remove('btn-vertical');

            if (lastEl.offsetTop > node.clientTop) {
                node.classList.add('btn-vertical');
            }
        },

        focus() {
            this.$refs.button[0].focus();
        },
    },
};
</script>
