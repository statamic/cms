<script setup>
import { Widget, Badge, Listing, Icon } from '@/components/ui';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    items: Object,
});
</script>

<template>
    <Listing :items="items" v-slot="{ items }">
        <Widget :title="__('Updates')" icon="updates" :href="cp_url('updater')">
            <table v-if="items.length" class="">
                <tr v-for="update in items" class="text-sm">
                    <td class="py-1 pr-4 leading-tight">
                        <Link :href="update.url" class="flex items-center gap-2" v-text="update.name" />
                    </td>
                    <td>
                        <Badge pill :color="update.critical ? 'red' : 'green'" :text="update.count" />
                        <div class="inline-flex" v-tooltip="__('Critical')">
                            <Icon v-if="update.critical" name="warning-diamond" color="red" />
                        </div>
                    </td>
                </tr>
            </table>
            <ui-description v-else class="flex-1 flex items-center justify-center">
                {{ __('Everything is up to date.') }}
            </ui-description>
        </Widget>
    </Listing>
</template>
