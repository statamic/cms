<script>
import Fieldtype from './Fieldtype.vue';
import { Button } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype],

    components: {
        Button,
    },
};
</script>

<template>
    <div class="mt-4 flex items-center gap-2">
        <Button
            v-if="config.options.length > 0"
            :href="blueprint.edit_url"
            variant="filled"
            size="sm"
            v-for="blueprint in config.options"
            :key="blueprint.handle"
        >
            {{ blueprint.title }}
        </Button>
        <Button size="sm" :href="config.all_blueprints_url">
            {{ __('View All Blueprints') }}
        </Button>
    </div>
</template>
