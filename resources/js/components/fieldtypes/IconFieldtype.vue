<template>
    <div class="flex icon-fieldtype-wrapper">
        <v-select
            v-if="!loading"
            ref="input"
            class="w-full"
            append-to-body
            :calculate-position="positionOptions"
            clearable
            :name="name"
            :disabled="config.disabled || isReadOnly"
            :options="options"
            :placeholder="__(config.placeholder || 'Search...')"
            :searchable="true"
            :multiple="false"
            :close-on-select="true"
            :value="selectedOption"
            :create-option="(value) => ({ value, label: value })"
            @input="vueSelectUpdated"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">
            <template slot="option" slot-scope="option">
                <div class="flex items-center">
                    <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="w-5 h-5" />
                    <div v-if="option.html" v-html="option.html" class="w-5 h-5" />
                    <span class="text-xs rtl:mr-4 ltr:ml-4 text-gray-800 dark:text-dark-150 truncate">{{ __(option.label) }}</span>
                </div>
            </template>
            <template slot="selected-option" slot-scope="option">
                <div class="flex items-center">
                    <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="w-5 h-5 flex items-center" />
                    <div v-if="option.html" v-html="option.html" class="w-5 h-5" />
                    <span class="text-xs rtl:mr-4 ltr:ml-4 text-gray-800 dark:text-dark-150 truncate">{{ __(option.label) }}</span>
                </div>
            </template>
        </v-select>
    </div>
</template>

<script>
import PositionsSelectOptions from '../../mixins/PositionsSelectOptions';
import { ref, watch } from 'vue';
const iconsCache = ref({});
const loaders = ref({});

export default {

    mixins: [Fieldtype, PositionsSelectOptions],

    data() {
        return {
            icons: [],
            loading: true,
        }
    },

    computed: {

        cacheKey() {
            return `${this.meta.directory}/${this.meta.set}`;
        },

        options() {
            let options = [];
            for (let [name, html] of Object.entries(this.icons)) {
                options.push({
                    value: name,
                    label: name,
                    html
                });
            }
            return options;
        },

        selectedOption() {
            return this.options.find(option => option.value === this.value);
        }
    },

    created() {
        this.request();

        watch(
            () => loaders.value[this.cacheKey],
            (loading) => {
                this.icons = iconsCache.value[this.cacheKey];
                this.loading = loading;
            }
        );
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        vueSelectUpdated(value) {
            if (value) {
                this.update(value.value)
            } else {
                this.update(null);
            }
        },

        request() {
            if (loaders.value[this.cacheKey]) return;

            loaders.value = {...loaders.value, [this.cacheKey]: true};

            this.$axios.post(this.meta.url, {
                config: utf8btoa(JSON.stringify(this.config)),
            }).then(response => {
                const icons = response.data.icons;
                this.icons = icons;
                iconsCache.value = {...iconsCache.value, [this.cacheKey]: icons};
            })
            .finally(() => {
                loaders.value = {...loaders.value , [this.cacheKey]: false};
            });
        }
    }
};
</script>
