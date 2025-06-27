<script setup>
import { ref, computed } from 'vue';
import {
    Header,
    Badge,
    Card,
    Panel,
    PanelFooter,
    Tabs,
    TabList,
    TabTrigger,
    Listing,
    ListingSearch as Search,
    ListingPagination as Pagination,
} from '@statamic/ui';

const props = defineProps(['domain', 'endpoints', 'installCount']);

const requestUrl = cp_url('/api/addons');
const filter = ref('all');
const showingAddon = ref(false);
const unlisted = ref([]);

const additionalParameters = computed(() => ({
    installed: filter.value === 'installed' ? 1 : 0,
}));

function getCover(addon) {
    return addon.assets.length
        ? addon.assets[0].url
        : 'https://statamic.com/images/img/marketplace/placeholder-addon.png';
}

function getPriceRange(addon) {
    let [low, high] = addon.price_range;
    low = low ? `$${low}` : 'Free';
    high = high ? `$${high}` : 'Free';
    return low == high ? low : `${low} - ${high}`;
}

function showAddon(addon) {
    showingAddon.value = addon;
    window.scrollTo(0, 0);
}
</script>

<template>
    <div>
        <Header v-if="!showingAddon" :title="__('Addons')" icon="addons" />
        <Listing
            v-if="!showingAddon"
            :url="requestUrl"
            :additional-parameters="additionalParameters"
            v-slot="{ items: addons, loading }"
        >
            <Tabs v-model="filter">
                <TabList>
                    <TabTrigger :text="__('All')" name="all" />
                    <TabTrigger name="installed">
                        {{ __('Installed') }}
                        <Badge size="sm" v-if="installCount" :text="installCount" />
                    </TabTrigger>
                </TabList>
            </Tabs>

            <div class="py-3">
                <Search />
            </div>

            <Panel>
                <div class="grid grid-cols-3 gap-2" :class="{ 'opacity-50': loading }">
                    <Card
                        class="relative cursor-pointer"
                        v-for="addon in addons"
                        :key="addon.id"
                        @click="showAddon(addon)"
                    >
                        <Badge
                            v-if="addon.installed"
                            :text="__('Installed')"
                            class="absolute top-0 mt-2 ltr:left-0 ltr:ml-2 rtl:right-0 rtl:mr-2"
                        />
                        <div
                            class="h-48 rounded-t bg-cover bg-center"
                            :style="'background-image: url(\'' + getCover(addon) + '\')'"
                        ></div>
                        <div class="relative mb-4 px-6 text-center">
                            <a :href="addon.seller.website" class="relative">
                                <img
                                    :src="addon.seller.avatar"
                                    :alt="addon.seller.name"
                                    class="dark:border-dark-600 dark:bg-dark-600 relative -mt-8 inline h-14 w-14 rounded-full border-2 border-white bg-white"
                                />
                            </a>
                            <div class="addon-card-title mb-2 text-center text-lg font-bold">{{ addon.name }}</div>
                            <p class="text-gray dark:text-dark-175 mb-4" v-text="getPriceRange(addon)" />
                            <p v-text="addon.summary" class="text-sm"></p>
                        </div>
                    </Card>
                </div>
                <PanelFooter>
                    <Pagination />
                </PanelFooter>
            </Panel>
        </Listing>
        <template v-if="unlisted.length && !showingAddon">
            <h6 class="mt-8">{{ __('Unlisted Addons') }}</h6>
            <div class="card mt-2 p-0">
                <table class="data-table">
                    <tbody>
                        <tr v-for="addon in unlisted" :key="addon.package">
                            <td v-text="addon.name" />
                            <td v-text="addon.package" />
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <addon-details
            v-if="showingAddon"
            :addon="showingAddon"
            :cover="getCover(showingAddon)"
            @close="showingAddon = false"
        />
    </div>
</template>
