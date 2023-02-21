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
            return this.portalTarget ? this.portalTarget.name : null;
        }

    },

    watch: {

        enabled(enabled) {
            this.$root.hideOverflow = enabled;

            if (enabled) {
                this.createPortalTarget();
            } else {
                this.destroyPortalTarget();
            }
        }

    },

    methods: {

        createPortalTarget() {
            let key = `fullscreen-${this.$parent.$options.name}-${this._uid}`;
            let portalTarget = { key, name: key };
            this.$root.portals.push(portalTarget);
            this.portalTarget = portalTarget;
        },

        destroyPortalTarget() {
            const i = _.findIndex(this.$root.portals, (portal) => portal.key === this.portalTarget.key);
            this.$root.portals.splice(i, 1);
        }
    }

}
</script>
