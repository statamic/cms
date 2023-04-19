<template>
    <div class="input-group max-w-[130px]">
        <div class="input-group-prepend px-px" v-tooltip="__('Pick Color')">
            <v-swatches
                fallback-input-type="color"
                swatch-size="38"
                v-model="color"
                :disabled="isReadOnly"
                :show-fallback="config.allow_any"
                :swatches="config.swatches"
            >
            </v-swatches>
        </div>
        <input
            class="input-text font-mono"
            maxlength="7"
            type="text"
            v-model="color"
            v-if="config.allow_any"
            :readonly="isReadOnly"
        />
    </div>
</template>

<script>
import VSwatches from 'vue-swatches'

export default {

    mixins: [Fieldtype],

    components: { VSwatches },

    data () {
        return {
            color: this.value
        }
    },

    watch: {

        value(value) {
            this.color = value;
        },

        color(color) {
            this.updateDebounced(color);
        }

    },

    computed: {

        replicatorPreview() {
            return this.value
                ? `<span class="little-dot" style="background-color:${this.value}"></span>`
                : null;
        }

    }

};
</script>
