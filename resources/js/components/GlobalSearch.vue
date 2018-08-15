<template>
    <div>
        <div class="global-search" :class="{'dirty': isDirty}" v-on-clickaway="reset" v-cloak>
            <div class="state-container w-4 h-4 text-grey-light" @click="focus">
                <slot name="icon"></slot>
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
                :placeholder="placeholder"
                />

            <i class="icon icon-cross" v-show="isDirty || searching" @click="reset"></i>

            <ul v-show="hasResults">
                <li v-for="(result, index) in results" :class="{ 'active': current == index }" @mousedown="hit" @mousemove="setActive(index)">
                    <span class="title" v-html="result.title"></span>
                    <span class="url" v-html="result.url"></span>
                </li>
            </ul>
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
            searching: false
        }
    },

    computed: {
        hasResults() {
            return this.results.length > 0;
        },

        isEmpty() {
            return !this.query && !this.searching;
        },

        isDirty() {
            return !!this.query && !this.searching;
        }
    },

    methods: {
        update() {
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
        },

        reset() {
            this.results = [];
            this.query = '';
            this.searching = false;
        },

        setActive(index) {
            this.current = index;
        },

        focus() {
            this.$refs.input.focus();
        },

        hit() {
            if (this.hasResults) {
                window.location.href = this.results[this.current].edit_url;
            }
        },

        moveUp() {
            if (this.current > 0) this.current--;
        },

        moveDown() {
            if (this.current < this.results.length-1) this.current++;
        }
    },

    watch: {
        query(query) {
            this.update();
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
