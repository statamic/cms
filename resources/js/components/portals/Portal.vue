<template>

    <v-portal
        name="popover"
        :to="portalTargetName"
        :target-class="targetClass"
        :disabled="disabled"
    >
        <provider :variables="provide">
           <slot />
        </provider>
    </v-portal>

</template>

<script>
import Provider from './Provider.vue';

export default {
    components: {
        Provider
    },

    props: {
        name: {
            type: String,
            required: true
        },
        provide: {
            type: Object
        },
        targetClass: {
            type: String
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            portalTarget: null,
        }
    },

    computed: {

        portalTargetName() {
            return this.portalTarget ? this.portalTarget.id : null;
        },

    },

    created() {
        this.portalTarget = this.$portals.create(this.name);
    },

    beforeDestroy() {
        this.portalTarget.destroy();
    }

}
</script>
