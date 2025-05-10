<template>
    <div class="flex items-center gap-2 mt-4">
        <Button v-if="config.options?.blueprints?.length > 0" :href="blueprint.edit_url" variant="filled" size="sm" v-for="blueprint in config.options.blueprints" :key="blueprint.handle">
            {{ blueprint.title }}
        </Button>
        <Button size="sm" :href="config.options.all_blueprints_url">
            {{ __('View All Blueprints') }}
        </Button>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Button } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: {
        Button,
    },
};
</script>
