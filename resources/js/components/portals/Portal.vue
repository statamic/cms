<template>

    <v-portal
        name="popover"
        :to="portal.id"
        :disabled="disabled"
    >
        <div class="vue-portal-target" :class="targetClass">
            <provider :variables="provide">
               <slot />
            </provider>
        </div>
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
            type: Object,
            default: () => ({})
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

    beforeUnmount() {
        this.portal.destroy();
    }

}
</script>
