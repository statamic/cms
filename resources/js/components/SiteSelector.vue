<template>

    <div class="site-selector">
        <dropdown-list>
            <template v-slot:trigger>
                <button class="flex outline-none items-center dropdown-toggle anti text-grey hover:text-grey-80">
                    <i class="site-selector-icon"><slot name="icon" /></i><span>{{ activeName }}</span>
                </button>
            </template>

            <li v-for="site in sites" :key="site.handle">
                <dropdown-item
                    :text="siteNameWithStatus(site)"
                    :class="{'text-grey hover:text-white': site.handle !== active}"
                    :redirect="cp_url(`select-site/${site.handle}`)" />
            </li>
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
    },

    methods: {
        siteNameWithStatus(site) {
            return [site.name, site.handle === this.active ? '(active)' : ''].join(' ').trim();
        }
    }

}
</script>
