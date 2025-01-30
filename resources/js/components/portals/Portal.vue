<template>

    <teleport
        :to="`#portal-target-${portal.id}`"
        :disabled="disabled"
        v-if="mounted"
    >
        <div class="vue-portal-target" :class="targetClass">
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
            mounted: false,
        }
    },

    created() {
        this.portal = this.$portals.create(this.name);
    },

    mounted() {
        this.mounted = true;
    },

    beforeUnmount() {
        this.portal.destroy();
    }

}
</script>
