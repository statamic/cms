<template>
    <div>
        <popover v-if="isNotYetFavorited" ref="popper" placement="bottom-end" :offset="[10, 28]">
            <template #trigger>
                <button
                    @click="shown"
                    slot="reference"
                    class="global-header-icon-button"
                    v-tooltip="__('Pin to Favorites')"
                    :aria-label="__('Pin to Favorites')"
                >
                    <svg-icon name="light/pin"></svg-icon>
                </button>
            </template>
            <div class="p-4 pb-2">
                <h6 class="mb-2">{{ __('Pin to Favorites') }}</h6>
                <div class="flex items-center">
                    <input type="text" class="input-text w-auto" ref="fave" v-model="name" @keydown.enter="save" />
                    <button @click="save" class="btn-primary ltr:ml-2 rtl:mr-2">{{ __('Save') }}</button>
                </div>
                <button @click="makeStartPage" class="mt-2 text-xs text-blue outline-hidden hover:text-blue-800">
                    {{ __('Set as start page') }} <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                </button>
            </div>
        </popover>
        <div v-else>
            <button
                @click="remove"
                class="global-header-icon-button"
                v-tooltip="__('Unpin from Favorites')"
                :aria-label="__('Unpin from Favorites')"
            >
                <svg-icon name="light/pin" class="text-green-600"></svg-icon>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            name: document.title.replace(
                ` ${this.$config.get('direction', 'ltr') === 'ltr' ? '‹' : '›'} ${__('Statamic')}`,
                '',
            ),
            currentUrl: this.$config.get('urlPath'),
        };
    },

    computed: {
        favorite() {
            return {
                name: this.name,
                url: this.currentUrl,
            };
        },

        persistedFavorite() {
            return this.$preferences.get('favorites', []).find((favorite) => {
                return favorite.url == this.currentUrl;
            });
        },

        isNotYetFavorited() {
            return this.persistedFavorite === undefined;
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },
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
            this.$preferences
                .append('favorites', this.favorite)
                .then((response) => {
                    this.saving = false;
                    this.$toast.success(__('Favorite saved'));
                    this.$refs.popper.close();
                    this.$events.$emit('favorites.added');
                })
                .catch((e) => {
                    this.saving = false;
                    if (e.response) {
                        this.$toast.error(e.response.data.message);
                    } else {
                        this.$toast.error(__('Unable to save favorite'));
                    }
                });
        },

        remove() {
            this.$preferences.remove('favorites', this.persistedFavorite).then((response) => {
                this.$toast.success(__('Favorite removed'));
            });
        },

        makeStartPage() {
            this.saving = true;
            this.$preferences
                .set('start_page', this.currentUrl)
                .then((response) => {
                    this.saving = false;
                    this.$toast.success(__('This is now your start page.'));
                    this.$refs.popper.close();
                    this.$events.$emit('start_page.saved');
                })
                .catch((e) => {
                    this.saving = false;
                    if (e.response) {
                        this.$toast.error(e.response.data.message);
                    } else {
                        this.$toast.error(__('Unable to save favorite'));
                    }
                });
        },
    },
};
</script>
