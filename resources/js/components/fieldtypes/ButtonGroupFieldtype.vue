<template>
    <div class="button-group-fieldtype-wrapper" :class="{'inline-mode': config.inline}">
        <div class="btn-group" ref="buttonGroup">
            <button class="btn px-4"
                v-for="(option, $index) in options"
                :key="$index"
                ref="button"
                type="button"
                :name="name"
                @click="updateSelectedOption(option.value)"
                :value="option.value"
                :disabled="isReadOnly"
                :class="{'active': value == option.value}"
                v-text="option.label || option.value"
            />
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js'
import ResizeObserver from 'resize-observer-polyfill';

export default {
    mixins: [Fieldtype, HasInputOptions],

    data() {
        return {
            resizeObserver: null,
        }
    },

    mounted() {
        this.setupResizeObserver();
    },

    beforeDestroy() {
        this.resizeObserver.disconnect();
    },

    computed: {
        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            var option = _.findWhere(this.options, {value: this.value});
            return (option) ? option.label : this.value;
        },
    },

    methods: {

        updateSelectedOption(newValue) {
            this.update(this.value == newValue && this.config.clearable ? null : newValue);
        },

        setupResizeObserver() {
            this.resizeObserver = new ResizeObserver(() => {
                this.handleWrappingOfNode(this.$refs.buttonGroup);
            });
            this.resizeObserver.observe(this.$refs.buttonGroup);
        },

        handleWrappingOfNode(node) {
            const lastEl = node.lastChild;

            if (!lastEl) return;

            node.classList.remove('btn-vertical');

            if(lastEl.offsetTop > node.clientTop) {
                node.classList.add('btn-vertical');
            }
        },

        focus() {
            this.$refs.button[0].focus();
        }

    }
};
</script>
