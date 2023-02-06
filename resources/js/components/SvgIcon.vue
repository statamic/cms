<template>
    <component v-if="icon" :is="icon" />
</template>

<script>
import { defineAsyncComponent } from 'vue';

export default {
    props: {
        name: String,
        default: String,
    },
    computed: {
        icon() {
            return defineAsyncComponent(() => import(`./../../svg/${this.name}.svg`)
                .catch(e => import(`./../../svg/${this.default}.svg`)
                    .catch(e => import('./../../svg/image.svg'))
                )
            );
        }
    }
}
</script>
