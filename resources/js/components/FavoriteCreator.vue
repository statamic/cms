<template>
    <div>
        <popover v-if="isNotYetFavorited" ref="popper" placement="auto-end" :offset="[28, 10]">
            <template slot="trigger">
                <button @click="shown" slot="reference" class="h-6 w-6 block outline-none p-sm text-grey hover:text-grey-80" v-tooltip="__('Pin to Favorites')" :aria-label="__('Pin to Favorites')">
                    <svg-icon name="pin"></svg-icon>
                </button>
            </template>
            <div class="p-2 pb-1">
                <h6 class="mb-1">{{ __('Pin to Favorites') }}</h6>
                <div class="flex items-center">
                    <input type="text" class="input-text w-auto" ref="fave" v-model="name" @keydown.enter="save" />
                    <button @click="save" class="btn-primary ml-1">{{ __('Save') }}</button>
                </div>
                <button @click="makeStartPage" class="mt-1 text-xs text-blue outline-none hover:text-blue-darker">{{ __('Set as start page') }} &rarr;</button>
            </div>
        </popover>
        <div v-else>
            <button @click="remove" class="h-6 w-6 block outline-none p-sm text-grey hover:text-grey-80" v-tooltip="__('Unpin from Favorites')" :aria-label="__('Unpin from Favorites')">
                <svg-icon name="pin" class="text-green"></svg-icon>
            </button>
        </div>
    </div>
</template>

<script>

export default {

    data() {
        return {
            name: document.title.replace(' ‹ Statamic', ''),
            currentUrl: this.$config.get('urlPath')
        }
    },

    computed: {
        favorite() {
            return {
                name: this.name,
                url: this.currentUrl
            }
        },

        persistedFavorite() {
            return _.find(this.$preferences.get('favorites'), favorite => {
                return favorite.url == this.currentUrl;
            });
        },

        isNotYetFavorited() {
            return this.persistedFavorite === undefined;
        }
    },

    methods: {
        shown() {
            this.highlight();
        },

        highlight() {
            setTimeout(() => this.$refs.fave.select(), 50);
        },

        save() {
            this.saving = true;
            this.$preferences.append('favorites', this.favorite).then(response => {
                this.saving = false;
                this.$toast.success(__('Favorite saved'));
                this.$refs.popper.close();
                this.$events.$emit('favorites.added');
            }).catch(e => {
                this.saving = false;
                if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(__('Unable to save favorite'));
                }
            });
        },

        remove() {
            this.$preferences.remove('favorites', this.persistedFavorite).then(response => {
                this.$toast.success(__('Favorite removed'));
            });
        },

        makeStartPage() {
            this.saving = true;
            this.$preferences.set('start_page', this.currentUrl).then(response => {
                this.saving = false;
                this.$toast.success(__('This is now your start page.'));
                this.$refs.popper.close();
                this.$events.$emit('start_page.saved');
            }).catch(e => {
                this.saving = false;
                if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(__('Unable to save favorite'));
                }
            });
        },
    }
}
</script>
