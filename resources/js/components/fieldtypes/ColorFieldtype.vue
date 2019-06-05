<template>
    <div class="color-picker"></div>
</template>


<style>
/* Temporary CSS patch */
.pcr-app .pcr-swatches {
    justify-content: start !important;
    grid-gap: 10px;
}
</style>


<script>
import '@simonwep/pickr/dist/pickr.min.css';
import Pickr from '@simonwep/pickr';

export default {

    mixins: [Fieldtype],

    mounted() {
        const pickr = Pickr.create({
            el: '.color-picker',
            components: {

                // Main components
                preview: true,
                opacity: true,
                hue: true,

                // Input / output Options
                interaction: {
                    hex: true,
                    rgba: true,
                    hsla: true,
                    hsva: true,
                    cmyk: true,
                    input: true,
                    clear: true,
                    save: true
                }
            },
            strings: {
                save: __('Save'),
                clear: __('Clear')
            },
            swatches: this.config.swatches
        });

        pickr.setColorRepresentation(this.config.default_mode);

        pickr.on('init', (...args) => {
            pickr.setColorRepresentation(this.config.default_mode);
            pickr.setColor(this.value);
        });

        pickr.on('save', (...args) => {
            var rep = args[1].getColorRepresentation();
            if (args[0] && rep) {
                // Dynamically call toHEX(), toRGBA(), etc
                this.update(args[0]['to' + rep]().toString());
            } else {
                // Color was manually cleared
                this.update(null);
            }
        });
    }

};
</script>
