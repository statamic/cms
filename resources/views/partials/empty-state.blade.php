<div class="no-results md:mt-4 max-w-md mx-auto">
    <div class="card rounded-xl text-center p-6 lg:py-10">
            <h1 class="mb-8">{{ $title }}</h1>

            <div class="hidden md:block">
                @cp_svg($svg ?? 'empty/content')
            </div>

            @if ($description ?? false)
            <p class="text-gray-700 leading-normal my-8 text-lg antialiased">
                {{ $description }}
            </p>
            @endif

            {{--
                Don't show it if there's no "button_url".
                If there's no "can", show it.
                If there's a "can", and its false, don't show it.
            --}}
            @unless (!isset($button_url) || (isset($can) && !$can))
                <a href="{{ $button_url }}" class="btn-primary btn-lg">{{ $button_text }}</a>
            @endunless
        </div>
</div>
