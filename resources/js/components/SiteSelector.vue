<template>

    <div class="site-selector">
        <dropdown-list>
            <button class="flex outline-none items-center dropdown-toggle anti text-grey hover:text-grey-80" slot="trigger">
                <i class="site-selector-icon"><slot name="icon" /></i><span>{{ activeName }}</span>
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
            return Statamic.$config.get('sites');
        },

        active() {
            return Statamic.$config.get('selectedSite');
        },

        activeName() {
            return _.findWhere(this.sites, { handle: this.active }).name;
        }

    }

}
</script>
