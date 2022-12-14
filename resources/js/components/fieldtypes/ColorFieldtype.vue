<template>
    <div class="color-picker"></div>
</template>

<script>
import '@simonwep/pickr/dist/themes/classic.min.css';
import '@simonwep/pickr/dist/themes/nano.min.css';
import Pickr from '@simonwep/pickr';

export default {

    mixins: [Fieldtype],

    computed: {

        replicatorPreview() {
            return this.value
                ? `<span class="little-dot" style="background-color:${this.value}"></span>`
                : null;
        }

    },

    mounted() {
        const pickr = new Pickr ({
            el: this.$el,
            disabled: this.isReadOnly,
            lockOpacity: this.config.lock_opacity,
            default: this.value ?? this.config.default ?? null,
            defaultRepresentation: this.config.default_color_mode,
            components: {

                // Main components
                preview: true,
                opacity: true,
                hue: true,

                // Input / output Options
                interaction: {
                    hex: this.config.color_modes.includes('hex'),
                    rgba: this.config.color_modes.includes('rgba'),
                    hsla: this.config.color_modes.includes('hsla'),
                    hsva: this.config.color_modes.includes('hsva'),
                    cmyk: this.config.color_modes.includes('cmyk'),
                    input: true,
                    clear: true,
                    save: true
                }
            },
            outputPrecision: 1,
            strings: {
                save: __('Save'),
                clear: __('Clear')
            },
            swatches: this.config.swatches,
            theme: this.config.theme || 'classic'
        });

        pickr.on('save', (...args) => {
            var rep = args[1].getColorRepresentation();
            if (args[0] && rep) {
                // Dynamically call toHEX(), toRGBA(), etc
                this.update(args[0]['to' + rep]().toString(0));
            } else {
                // Color was manually cleared
                this.update(null);
            }
            pickr.hide();
        });
    }

};
</script>
