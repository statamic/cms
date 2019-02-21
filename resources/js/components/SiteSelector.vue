<template>

    <div class="flex items-center px-2 border-l h-full text-sm">
        <dropdown-list>
            <button class="flex outline-none items-center dropdown-toggle anti text-grey hover:text-grey-80" slot="trigger">
                <i class="block h-6 w-6 mr-1"><slot name="icon" /></i><span>{{ activeName }}</span>
            </button>
            <ul class="dropdown-menu">
                <li v-for="site in sites" :key="site.handle">
                    <a
                        :href="cp_url(`select-site/${site.handle}`)"
                        :class="{'text-grey hover:text-white': site.handle !== active}">
                        {{ site.name }}
                        <template v-if="site.handle === active">(active)</template>
                    </a>
                </li>
            </ul>
        </dropdown-list>
    </div>

</template>

<script>
export default {

    computed: {

        sites() {
            return Statamic.sites;
        },

        active() {
            return Statamic.selectedSite;
        },

        activeName() {
            return _.findWhere(this.sites, { handle: this.active }).name;
        }

    }

}
</script>
