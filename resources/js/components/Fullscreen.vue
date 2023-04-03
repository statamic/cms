<template>

    <div>
        <portal :to="portalTargetName" :disabled="!enabled" :target-class="targetClass">
            <provider :variables="provide">
                <slot />
            </provider>
        </portal>
    </div>

</template>

<script>
import Provider from './live-preview/Provider.vue';
export default {

    components: {
        Provider
    },

    props: {
        enabled: {
            type: Boolean,
            default: false
        },
        provide: {
            type: Object
        },
        targetClass: {
            type: String
        }
    },

    data() {
        return {
            portalTarget: null
        }
    },

    computed: {

        portalTargetName() {
            return this.portalTarget ? this.portalTarget.id : null;
        }

    },

    watch: {

        enabled(enabled) {
            this.$root.hideOverflow = enabled;

            if (enabled) {
                this.portalTarget = this.$portals.create(`fullscreen-${this.$parent.$options.name}`);
            } else {
                this.portalTarget.destroy();
            }
        }

    }

}
</script>
