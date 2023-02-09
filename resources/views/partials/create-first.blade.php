<div class="no-results border-dashed border-2">
    <div class="text-center max-w-md mx-auto mt-10___REPLACED rounded-lg px-4 py-8">

        @cp_svg($svg)

        <h1 class="my-6___REPLACED">
            @if ($can ?? $user->can('super'))
                {{ __("Create your first {$resource} now") }}
            @else
                {{ __('No ' . Statamic\Support\Str::plural($resource) . ' exist yet') }}
            @endif
        </h1>

        @if ($description ?? false)
            <p class="text-grey mb-6___REPLACED">
                {{ __($description) }}
            </p>
        @endif

        @if ($can ?? $user->can('super'))
            @if($button ?? false)
                {{ $button }}
            @else
                <a href="{{ $route ?? null }}" class="btn-primary btn-lg">{{ __("Create {$resource}") }}</a>
            @endif
        @endif

    </div>
</div>
