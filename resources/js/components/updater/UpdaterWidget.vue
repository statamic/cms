<script setup>
import { Widget, Badge, Listing, Icon, Tooltip } from '@statamic/ui';
import { ref } from 'vue';

defineProps({
    items: Object,
});
</script>

<template>
    <Listing :items="items" v-slot="{ items }">
        <Widget :title="__('Updates')" icon="updates">
            <table v-if="items.length" class="">
                <tr v-for="update in items" class="text-sm">
                    <td class="py-1 pr-4 leading-tight">
                        <a :href="update.url" class="flex items-center gap-2" v-text="update.name" />
                    </td>
                    <td>
                        <Badge pill variant="flat" :color="update.critical ? 'red' : 'green'" :text="update.count" />
                        <Tooltip :text="__('Critical')">
                            <div class="inline-flex">
                                <Icon v-if="update.critical" name="warning-diamond" color="red" />
                            </div>
                        </Tooltip>
                    </td>
                </tr>
            </table>
            <p v-else class="p-3 text-center text-sm text-gray-600">
                {{ __('Everything is up to date.') }}
            </p>
        </Widget>
    </Listing>
</template>
