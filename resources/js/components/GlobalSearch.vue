<template>
    <div>
        <div class="global-search" :class="{'dirty': isDirty}" v-on-clickaway="reset" v-cloak>
            <div class="state-container w-4 h-4 text-grey-40" @click="focus">
                <svg-icon name="magnifying-glass"></svg-icon>
            </div>

            <input type="text"
                autocomplete="off"
                class="search-input"
                ref="input"
                v-model="query"
                @keydown.up.prevent="moveUp"
                @keydown.down.prevent="moveDown"
                @keydown.enter.prevent="hit"
                @keydown.esc.prevent="reset"
                @focus="focused = true"
                :placeholder="placeholder"
                />

            <span v-if="! (isDirty || searching)" class="rounded px-sm text-2xs border text-grey-40">/</span>

            <div v-show="focused && (hasResults || hasFavorites)" class="global-search-results">

                <div v-if="hasResults" v-for="(result, index) in results" class="global-search-result-item flex items-center" :class="{ 'active': current == index }" @mousedown="hit" @mousemove="setActive(index)">
                    <svg-icon :name="getResultIcon(result)" class="icon"></svg-icon>
                    <div class="flex-1 ml-1 title" v-html="result.title"></div>
                    <span class="rounded px-sm py-px text-2xs uppercase bg-grey-20 text-grey" v-html="result.collection"></span>
                </div>

                <div v-if="! hasResults && hasFavorites">
                    <div class="px-1.5 py-1 text-grey uppercase text-3xs">{{ __('Your Favorites') }}</div>

                    <div v-for="(favorite, index) in favorites" class="global-search-result-item flex items-center" :class="{ 'active': current == index }" @mousedown="hit" @mousemove="setActive(index)">
                        <svg-icon name="pin" class="icon"></svg-icon>
                        <div class="flex-1 ml-1 title" v-html="favorite.name"></div>
                    </div>

                    <div class="text-grey text-xs px-1.5 py-1 border-t text-center"><b class="tracking-wide uppercase text-3xs">{{ __('Pro Tip')}}:</b> You can open global search using the <span class="rounded px-sm text-2xs border text-grey-40">/</span> key</div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
import { mixin as clickaway } from 'vue-clickaway';

export default {
    mixins: [ clickaway ],

    props: {
        limit: Number,
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
                this.reset();
                return;
            }

            this.searching = true;

            let payload = {params: Object.assign({ q:this.query }, this.data) };

            this.axios.get(this.endpoint, payload)
                .then(response => {
                    this.results = !!this.limit ? response.data.slice(0, this.limit) : response.data;
                    this.current = -1;
                    this.searching = false;
                });
        }, 300),

        reset() {
            this.results = [];
            this.query = '';
            this.searching = false;
            this.focused = false;
        },

        setActive(index) {
            this.current = index;
        },

        focus() {
            this.$refs.input.focus();
            this.focused = true;
        },

        hit() {
            if (this.hasResults) {
                window.location.href = this.results[this.current].edit_url;
            } else {
                window.location.href = this.favorites[this.current].url;
            }
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
            return 'content-writing';
        }
    },

    watch: {
        query(query) {
            this.update();
        },

        searching(searching) {
            // this.$progress.loading('global-search', searching);
        }
    },

    mounted() {
        this.$mousetrap.bind(['/', 'ctrl+f', 'alt+f', 'shift+f'], e => {
            e.preventDefault();
            this.focus();
        });
    }
};
</script>
