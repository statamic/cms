<template>
    <div class="flex items-center">
        <v-swatches
            fallback-input-type="color"
            swatch-size="38"
            v-model="color"
            :disabled="isReadOnly"
            :show-fallback="config.allow_any"
            :swatches="config.swatches"
        >
        </v-swatches>
        <input
            class="input-text ml-2 w-24 font-mono"
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
            color: this.config.default
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

    },

    mounted() {
        this.color = this.value
    },
};
</script>
