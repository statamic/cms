<template>
    <div class="global-search" :class="{ dirty: isDirty }" v-click-away="reset" v-cloak>
        <div class="state-container flex h-4 w-4 items-center text-gray-500" @click="focus">
            <svg-icon name="light/magnifying-glass" class="h-4 w-4"></svg-icon>
        </div>
        <label class="sr-only" v-text="__('Global Search')" for="global-search" />
        <input
            type="text"
            autocomplete="off"
            class="search-input"
            ref="input"
            name="search"
            v-model="query"
            id="global-search"
            @keydown.up.prevent="moveUp"
            @keydown.down.prevent="moveDown"
            @keydown.enter.prevent="hit"
            @keydown.esc.prevent="reset"
            @focus="focused = true"
            :placeholder="placeholder"
            tabindex="-1"
        />

        <span
            v-if="!(isDirty || searching)"
            class="rounded border px-1 pb-px text-2xs text-gray-600 dark:border-dark-300 dark:text-dark-200"
            >/</span
        >
        <loading-graphic v-if="searching" :size="14" :inline="true" text="" class="global-search-loading-indicator" />

        <div v-show="focused && (hasResults || hasFavorites)" class="global-search-results">
            <div
                v-if="hasResults"
                v-for="(result, index) in results"
                class="global-search-result-item break-overflowing-words flex items-start p-2"
                :class="{ active: current == index }"
                @click="hit"
                @mousemove="setActive(index)"
            >
                <svg-icon :name="`light/${getResultIcon(result)}`" class="icon"></svg-icon>
                <div class="title flex-1 ltr:ml-2 rtl:mr-2" v-html="result.title"></div>
                <span class="global-search-result-badge" v-text="result.badge" />
            </div>

            <div v-if="!hasResults && hasFavorites">
                <div class="px-3 py-2 text-3xs uppercase text-gray dark:text-dark-200">{{ __('Your Favorites') }}</div>

                <div
                    v-for="(favorite, index) in favorites"
                    class="global-search-result-item flex items-center"
                    :class="{ active: current == index }"
                    @mousemove="setActive(index)"
                >
                    <div class="flex flex-1 items-center p-2" @click="hit">
                        <svg-icon name="light/pin" class="h-4 w-4"></svg-icon>
                        <div class="title ltr:ml-2 rtl:mr-2" v-text="favorite.name"></div>
                    </div>
                    <div class="p-2 text-gray-600 hover:text-gray-800" @click="removeFavorite(favorite)">&times;</div>
                </div>

                <div class="border-t px-3 py-2 text-center text-xs text-gray dark:border-dark-900 dark:text-dark-200">
                    <b class="text-3xs uppercase tracking-widest">{{ __('Pro Tip') }}:</b>
                    <span v-html="__('messages.global_search_open_using_slash')" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { debounce } from 'lodash-es';

export default {
    props: {
        endpoint: String,
        placeholder: String,
    },

    data() {
        return {
            results: [],
            query: '',
            current: -1,
            searching: false,
            focused: false,
        };
    },

    computed: {
        hasResults() {
            return this.results.length > 0;
        },

        favorites() {
            return this.$preferences.get('favorites', []);
        },

        hasFavorites() {
            return this.favorites.length > 0;
        },

        isEmpty() {
            return !this.query && !this.searching;
        },

        isDirty() {
            return !!this.query && !this.searching;
        },
    },

    methods: {
        update: debounce(function () {
            if (!this.query) {
                this.results = [];
                this.searching = false;
                return;
            }

            let payload = { params: { q: this.query } };

            this.$axios.get(this.endpoint, payload).then((response) => {
                this.results = response.data;
                this.current = -1;
                this.searching = false;
            });
        }, 300),

        reset() {
            this.results = [];
            this.query = '';
            this.focused = false;
            this.searching = false;
        },

        setActive(index) {
            this.current = index;
        },

        focus() {
            this.$refs.input.focus();
            this.focused = true;
        },

        hit($event) {
            const item = this.hasResults ? this.results[this.current] : this.favorites[this.current];

            if (!item) return;

            const url = this.hasResults ? item.url : `${this.$config.get('cpRoot')}/${item.url}`;

            $event.metaKey ? window.open(url) : (window.location = url);
        },

        moveUp() {
            if (this.current > 0) this.current--;
        },

        moveDown() {
            if (this.hasResults) {
                if (this.current < this.results.length - 1) this.current++;
            } else {
                if (this.current < this.favorites.length - 1) this.current++;
            }
        },

        getResultIcon(result) {
            if (result.reference.startsWith('asset::')) {
                return 'assets';
            } else if (result.reference.startsWith('user::')) {
                return 'user';
            } else {
                return 'content-writing';
            }
        },

        removeFavorite(favorite) {
            this.$preferences.remove('favorites', favorite).then((response) => {
                this.$toast.success(__('Favorite removed'));
            });
        },
    },

    watch: {
        query(query) {
            this.searching = true;
            this.update();
        },

        searching(searching) {
            // this.$progress.loading('global-search', searching);
        },
    },

    created() {
        this.$events.$on('favorites.added', this.focus);
    },

    mounted() {
        this.$keys.bind('/', (e) => {
            e.preventDefault();
            this.focus();
        });
    },
};
</script>
