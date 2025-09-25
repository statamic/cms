<script setup>
</script>

<template>
    [logo]

<!--    @if ($customDarkLogo)-->
<!--    <button class="flex items-center group cursor-pointer text-white/85 hover:text-white" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">-->
<!--        <div class="p-1 size-7 inset-0 flex items-center justify-center">-->
<!--            @cp_svg('icons/burger-menu', 'size-5')-->
<!--        </div>-->
<!--    </button>-->
<!--    <img src="{{ $customDarkLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="max-w-[260px] max-h-9">-->
<!--    @else-->
<!--    <div class="flex items-center gap-2 relative">-->
<!--        <button class="flex items-center group rounded-lg cursor-pointer" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}" style="&#45;&#45;focus-outline-offset: 0.2rem;">-->
<!--            <div class="opacity-0 group-hover:opacity-100 p-1 size-7 transition-opacity duration-150 absolute inset-0 flex items-center justify-center">-->
<!--                @cp_svg('icons/burger-menu', 'size-5')-->
<!--            </div>-->
<!--            @cp_svg('statamic-mark-lime', 'size-7 group-hover:opacity-0 transition-opacity duration-150')-->
<!--        </button>-->
<!--        <a href="{{ route('statamic.cp.index') }}" class="hidden sm:block text-white/85 rounded-xs whitespace-nowrap" style="&#45;&#45;focus-outline-offset: var(&#45;&#45;outline-offset-button);">-->
<!--            {{ $customLogoText ?? config('app.name') }}-->
<!--        </a>-->
<!--        @if (Statamic::pro())-->
<!--        <x-statamic::pro-badge />-->
<!--        @endif-->
<!--    </div>-->
<!--    @endif-->
</template>
