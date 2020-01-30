<template>
    <div>
        <popper v-if="isNotYetFavorited" ref="popper" @show="highlight" trigger="click" :append-to-body="true" :options="{ placement: 'bottom' }">

            <div class="card p-0 shadow-lg z-top">
                <div class="flex justify-between text-center">
                    <h6 class="whitespace-no-wrap w-40 cursor-pointer p-1 border-r" :class="{'border-b bg-grey-10': ! showingPinTab }" @click="showingPinTab = true">
                        {{ __('Pin to Favorites') }}
                    </h6>
                    <h6 class="whitespace-no-wrap w-40 cursor-pointer p-1" :class="{'border-b bg-grey-10': showingPinTab }" @click="showingPinTab = false">
                        {{ __('Make Start Page') }}
                    </h6>
                </div>
                <div class="p-2 flex items-center" v-if="showingPinTab">
                    <input type="text" class="input-text" autofocus ref="fave" v-model="name" @keydown.enter="save">
                    <button @click="save" class="btn-primary ml-1">{{ __('Save') }}</button>
                </div>
                <div class="p-2" v-else>
                    <button @click="makeStartPage" class="btn block w-full">{{ __('Start here when you sign in') }}</button>
                </div>
            </div>

            <button slot="reference" class="h-6 w-6 block outline-none p-sm text-grey hover:text-grey-80" v-tooltip="__('Pin to Favorites')">
                <svg-icon name="pin"></svg-icon>
            </button>
        </popper>
        <div v-else>
            <button @click="remove" class="h-6 w-6 block outline-none p-sm text-grey hover:text-grey-80" v-tooltip="__('Unpin from Favorites')">
                <svg-icon name="pin"></svg-icon>
            </button>
        </div>
    </div>
</template>

<script>
import Popper from 'vue-popperjs';

export default {

    components: {
        Popper
    },

    data() {
        return {
            name: document.title.replace(' ‹ Statamic', ''),
            currentUrl: this.$config.get('urlPath').substr(this.$config.get('cpRoot').length+1),
            showingPinTab: true,
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
        highlight() {
            setTimeout(() => this.$refs.fave.select(), 20);
        },

        toggleTab() {
            this.showingPinTab = ! this.showingPinTab;
        },

        save() {
            this.saving = true;
            this.$preferences.append('favorites', this.favorite).then(response => {
                this.saving = false;
                this.$toast.success(__('Favorite saved'));
                this.$refs.popper.doClose();
                this.$events.$emit('favorites.added');
            }).catch(e => {
                this.saving = false;
                if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(__('Something went wrong'));
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
                this.$refs.popper.doClose();
                this.$events.$emit('start_page.saved');
            }).catch(e => {
                this.saving = false;
                if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(__('Something went wrong'));
                }
            });
        },
    },

    mounted() {
        this.$keys.bindGlobal(['esc'], e => {
            this.$refs.popper.doClose();
        });
    }
}
</script>
