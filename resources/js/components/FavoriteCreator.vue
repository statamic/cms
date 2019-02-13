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

    data() {
        return {
            name: document.title
        }
    },

    methods: {
        highlight() {
            this.$refs.fave.select();
        },
        save() {
            console.log('This is the part of our tale where the data is saved, like a princess in a tall tower guarded by a misunderstood dragon.');

            this.$refs.popper.doClose()
        }
    }
}
</script>
