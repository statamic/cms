<template>
    <div>
        <popper ref="popper" @show="highlight" trigger="click" :append-to-body="true" :options="{ placement: 'bottom' }">

            <div class="card p-0 shadow-lg z-top">
                <h6 class="text-center p-1 border-b">{{ __('Pin to Favorites') }}</h6>
                <div class="p-2 flex items-center">
                    <input type="text" class="input-text" autofocus ref="fave" v-model="name" @keydown.enter="save">
                    <button @click="save" class="btn-primary ml-1">{{ __('Save') }}</button>
                </div>
            </div>

            <button slot="reference" class="h-6 w-6 block outline-none p-sm text-grey hover:text-grey-dark" v-popover:tooltip.bottom="__('Pin to Favorites')">
                <svg-icon name="pin"></svg-icon>
            </button>
        </popper>
    </div>
</template>

<script>
import Popper from 'vue-popperjs';

export default {

    components: {
        Popper
    },

    props: {
        currentUrl: String,
        saveUrl: String
    },

    data() {
        return {
            name: document.title
        }
    },

    computed: {
        favorite() {
            return {
                name: this.name,
                url: this.currentUrl
            }
        }
    },

    methods: {
        highlight() {
            this.$refs.fave.select();
        },

        save() {
            this.saving = true;
            this.axios.post(this.saveUrl, this.favorite).then(response => {
                this.saving = false;
                this.$notify.success(__('Favorite saved'), { timeout: 3000 });
                this.$refs.popper.doClose();
            }).catch(e => {
                this.saving = false;
                if (e.response) {
                    this.$notify.error(e.response.data.message);
                } else {
                    this.$notify.error(__('Something went wrong'));
                }
            });
        }
    }
}
</script>
