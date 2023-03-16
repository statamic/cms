<template>
    <div class="global-search" :class="{'dirty': isDirty}" v-on-clickaway="reset" v-cloak>
        <div class="state-container w-4 h-4 text-grey-50" @click="focus">
            <svg-icon name="magnifying-glass"></svg-icon>
        </div>
        <label class="sr-only" v-text="__('Global Search')" for="global-search" />
        <input type="text"
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

        <span v-if="! (isDirty || searching)" class="rounded px-sm pb-px text-2xs border text-grey-60">/</span>
        <loading-graphic v-if="searching" :size="14" :inline="true" text="" class="global-search-loading-indicator" />

        <div v-show="focused && (hasResults || hasFavorites)" class="global-search-results">

            <div v-if="hasResults" v-for="(result, index) in results" class="global-search-result-item p-1 flex items-center" :class="{ 'active': current == index }" @click="hit" @mousemove="setActive(index)">
                <svg-icon :name="getResultIcon(result)" class="icon"></svg-icon>
                <div class="flex-1 ml-1 title" v-html="result.title"></div>
                <span class="rounded px-sm py-px text-2xs uppercase bg-grey-20 text-grey" v-text="result.badge" />
            </div>

            <div v-if="! hasResults && hasFavorites">
                <div class="px-1.5 py-1 text-grey uppercase text-3xs">{{ __('Your Favorites') }}</div>

                <div v-for="(favorite, index) in favorites" class="global-search-result-item flex items-center" :class="{ 'active': current == index }" @mousemove="setActive(index)">
                    <div class="flex items-center flex-1 p-1" @click="hit">
                        <svg-icon name="pin" class="icon w-4 h-4"></svg-icon>
                        <div class="ml-1 title" v-text="favorite.name"></div>
                    </div>
                    <div class="p-1 text-grey-60 hover:text-grey-80" @click="removeFavorite(favorite)">&times;</div>
                </div>

                <div class="text-grey text-xs px-1.5 py-1 border-t text-center">
                    <b class="tracking-wide uppercase text-3xs">{{ __('Pro Tip')}}:</b>
                    <span v-html="__('messages.global_search_open_using_slash')" />
                </div>
            </div>
        </div>
        <portal to="outside" v-if="focused">
            <div class="absolute inset-0 h-full w-full bg-darken-10 z-10"></div>
        </portal>
    </div>
</template>


<script>
import { mixin as clickaway } from 'vue-clickaway';

export default {
    mixins: [ clickaway ],

    props: {
        endpoint: String,
        placeholder: String
    },

    data() {
        return {
            results: [],
            query: '',
            current: -1,
            searching: false,
            focused: false
        }
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
        update: _.debounce(function () {
            if (!this.query) {
                this.results = [];
                this.searching = false;
                return;
            }

            let payload = {params: { q: this.query }};

            this.$axios.get(this.endpoint, payload)
                .then(response => {
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

            $event.metaKey ? window.open(url) : window.location = url;
        },

        moveUp() {
            if (this.current > 0) this.current--;
        },

        moveDown() {
            if (this.hasResults) {
                if (this.current < this.results.length-1) this.current++;
            } else {
                if (this.current < this.favorites.length-1) this.current++;
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
            this.$preferences.remove('favorites', favorite).then(response => {
                this.$toast.success(__('Favorite removed'));
            });
        }
    },

    watch: {
        query(query) {
            this.searching = true;
            this.update();
        },

        searching(searching) {
            // this.$progress.loading('global-search', searching);
        }
    },

    created() {
        this.$events.$on('favorites.added', this.focus);
    },

    mounted() {
        this.$keys.bind('/', e => {
            e.preventDefault();
            this.focus();
        });
    }
};
</script>
