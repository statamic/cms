@props(['title', 'description', 'svg', 'button_text', 'button_url', 'can'])

<div class="no-results mx-auto max-w-md md:mt-4">
    <div class="card rounded-xl p-6 text-center lg:py-10">
        <h1 class="mb-8">{{ $title }}</h1>

        <div class="hidden md:block">
            @cp_svg($svg ?? 'empty/content')
        </div>

        @if ($description ?? false)
            <p class="my-8 text-lg leading-normal text-gray-700 antialiased">
                {{ $description }}
            </p>
        @endif

        {{--
            Don't show it if there's no "button_url".
            If there's no "can", show it.
            If there's a "can", and its false, don't show it.
        --}}
        @unless (! isset($button_url) || (isset($can) && ! $can))
            <a href="{{ $button_url }}" class="btn-primary btn-lg">{{ $button_text }}</a>
        @endunless
    </div>
</div>
