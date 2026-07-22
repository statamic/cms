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
            <div v-if="items.length" class="w-full px-4 py-3">
                <table class="w-full">
                    <tr v-for="update in items" class="text-sm">
                        <td class="py-1 pr-4 leading-tight">
                            <Link :href="update.url" class="flex items-center gap-2" v-text="update.name" />
                        </td>
                        <td class="text-right">
                            <Badge
                                pill
                                :text="update.count"
                                :color="update.security ? 'red' : 'amber'"
                                v-tooltip="update.security ? __('Security update available') : null"
                            />
                        </td>
                    </tr>
                </table>
            </div>
            <ui-description v-else class="flex-1 flex items-center justify-center">
                {{ __('Everything is up to date.') }}
            </ui-description>
        </Widget>
    </Listing>
</template>
