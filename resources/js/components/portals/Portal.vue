<template>
    <teleport
        v-if="portal"
        to="#portals"
        :name="name"
        :disabled="disabled"
    >
        <div :class="['vue-portal-target', targetClass]">
            <provider :variables="provide">
                <slot />
            </provider>
        </div>
    </teleport>
</template>

<script>
import Provider from './Provider.vue';

export default {
    components: {
        Provider,
    },

    props: {
        name: {
            type: String,
            required: true
        },
        provide: {
            type: Object,
            default: () => {},
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
        };
    },

    async created() {
        this.portal = await this.$store.dispatch('portals/create', { name: this.name });
    },

    beforeDestroy() {
        this.portal.destroy();
    }

};
</script>
