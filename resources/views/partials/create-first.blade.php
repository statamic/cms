<div class="border-dashed border-2">
    <div class="text-center max-w-md mx-auto mt-5 rounded-lg px-4 py-8">

        @svg($svg)

        <h1 class="my-3">{{ __("Create your first {$resource} now") }}</h1>

        @if ($description ?? false)
            <p class="text-grey mb-3">
                {{ __($description) }}
            </p>
        @endif

        @can($can ?? 'super')
            @if($button ?? false)
                {{ $button }}
            @else
                <a href="{{ $route ?? null }}" class="btn-primary btn-lg">{{ __("Create {$resource}") }}</a>
            @endif
        @endcan

    </div>
</div>
