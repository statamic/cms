@php
    use function Statamic\trans as __;
@endphp

<div class="no-results border-2 border-dashed">
    <div class="mx-auto mt-10 max-w-md rounded-lg px-8 py-30 text-center">
        @cp_svg($svg)

        <h1 class="my-6">
            @if ($can ?? $user->can('super'))
                {{ __("Create your first {$resource} now") }}
            @else
                {{ __('No ' . Statamic\Support\Str::plural($resource) . ' exist yet') }}
            @endif
        </h1>

        @if ($description ?? false)
            <p class="mb-6 text-gray">
                {{ __($description) }}
            </p>
        @endif

        @if ($can ?? $user->can('super'))
            @if ($button ?? false)
                {{ $button }}
            @else
                <a href="{{ $route ?? null }}" class="btn-primary btn-lg">{{ __("Create {$resource}") }}</a>
            @endif
        @endif
    </div>
</div>
