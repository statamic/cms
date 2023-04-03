<template>

    <v-portal
        name="popover"
        :to="portal.id"
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
            portal: null,
        }
    },

    created() {
        this.portal = this.$portals.create(this.name);
    },

    beforeDestroy() {
        this.portal.destroy();
    }

}
</script>
