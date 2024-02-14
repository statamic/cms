<div class="no-results border-dashed border-2">
    <div class="text-center max-w-md mx-auto mt-10 rounded-lg px-8 py-30">

        @cp_svg($svg)

        <h1 class="my-6">
            @if ($can ?? $user->can('super'))
                {{ Statamic\trans("Create your first {$resource} now") }}
            @else
                {{ Statamic\trans('No ' . Statamic\Support\Str::plural($resource) . ' exist yet') }}
            @endif
        </h1>

        @if ($description ?? false)
            <p class="text-gray mb-6">
                {{ Statamic\trans($description) }}
            </p>
        @endif

        @if ($can ?? $user->can('super'))
            @if($button ?? false)
                {{ $button }}
            @else
                <a href="{{ $route ?? null }}" class="btn-primary btn-lg">{{ Statamic\trans("Create {$resource}") }}</a>
            @endif
        @endif

    </div>
</div>
