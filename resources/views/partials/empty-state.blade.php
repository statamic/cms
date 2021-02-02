<div class="no-results md:pt-8 max-w-2xl mx-auto">
    <div class="flex flex-wrap">
        <div class="w-full md:w-1/2">
            <h1 class="mb-4">{{ $title }}</h1>

            @if ($description ?? false)
            <p class="text-grey-70 leading-normal mb-4 text-lg antialiased">
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
        <div class="hidden md:block w-1/2 pl-6">
            @cp_svg($svg ?? 'empty/content')
        </div>
    </div>
</div>
