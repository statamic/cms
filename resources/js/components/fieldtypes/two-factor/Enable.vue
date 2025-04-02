<template>
    <div>
        <div class="mb-8">
            <div class="font-semibold mb-2">{{ __('statamic-two-factor::profile.enable.title') }}</div>

            <div class="text-xs text-gray-700 mb-4">
                <p class="mb-1">{{ __('statamic-two-factor::profile.enable.intro') }}</p>
            </div>

            <div class="flex space-x-2">
                <a :href="route" class="btn">{{ __('statamic-two-factor::profile.enable.enable') }}</a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        route: {
            type: String,
            required: true
        }
    },
};
</script>
