<template>
    <div class="flex flex-nowrap space-x-4 text-xs">
        <div v-if="status.locked" class="flex items-center flex-nowrap">
            <svg class="w-4 h-4 text-orange" fill="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17 9V7c0-2.8-2.2-5-5-5S7 4.2 7 7v2c-1.7 0-3 1.3-3 3v7c0 1.7 1.3 3 3 3h10c1.7 0 3-1.3 3-3v-7c0-1.7-1.3-3-3-3zM9 7c0-1.7 1.3-3 3-3s3 1.3 3 3v2H9V7z"/>
            </svg>
            <span class="ml-1 font-medium text-orange">{{ __('statamic-two-factor::fieldtype.status.locked') }}</span>
        </div>

        <div v-else-if="status.setup" class="flex items-center flex-nowrap">
            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 512 512"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M256 48C141.1 48 48 141.1 48 256s93.1 208 208 208 208-93.1 208-208S370.9 48 256 48zm-32.1 281.7c-2.4 2.4-5.8 4.4-8.8 4.4s-6.4-2.1-8.9-4.5l-56-56 17.8-17.8 47.2 47.2L340 177.3l17.5 18.1-133.6 134.3z"></path>
            </svg>
            <span class="ml-1">{{ __('statamic-two-factor::fieldtype.status.set_up') }}</span>
        </div>

        <div v-else class="flex items-center flex-nowrap">
            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 512 512"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M403.1 108.9c-81.2-81.2-212.9-81.2-294.2 0s-81.2 212.9 0 294.2c81.2 81.2 212.9 81.2 294.2 0s81.2-213 0-294.2zM352 340.2L340.2 352l-84.4-84.2-84 83.8-11.8-11.8 84-83.8-84-83.8 11.8-11.8 84 83.8 84.4-84.2 11.8 11.8-84.4 84.2 84.4 84.2z"></path>
            </svg>
            <span class="ml-1">{{ __('statamic-two-factor::fieldtype.status.not_set_up') }}</span>
        </div>
    </div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';

export default {
    mixins: [IndexFieldtype],

    computed: {
        status() {
            try {
                return JSON.parse(this.value);
            } catch (e) {
                return this.value;
            }
        }
    }
}
</script>
