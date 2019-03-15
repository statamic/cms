<div class="border-dashed border-2">
    <div class="text-center max-w-md mx-auto mt-5 rounded-lg px-4 py-8">
        @svg($svg)
        <h1 class="my-3">{{ __("Create your first {$resource} now") }}</h1>
        <p class="text-grey mb-3">
            {{ __($description) }}
        </p>
        @can($can ?? 'super')
            <a href="{{ $route }}" class="btn-primary btn-lg">{{ __("Create {$resource}") }}</a>
        @endcan
    </div>
</div>
