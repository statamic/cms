<template>
    <div>
        <div class="mb-8">
            <div class="mb-2 font-semibold">{{ __('Enable two factor authentication') }}</div>

            <div class="mb-4 text-xs text-gray-700">
                <p class="mb-1">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>
            </div>

            <div class="flex space-x-2">
                <a :href="route" class="btn">{{ __('Set up two factor authentication') }}</a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        route: {
            type: String,
            required: true,
        },
    },
};
</script>
