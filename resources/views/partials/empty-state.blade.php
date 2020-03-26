<div class="no-results md:pt-8 max-w-2xl mx-auto">
    <div class="flex flex-wrap">
        <div class="w-full md:w-1/2">
            <h1 class="mb-4">
                @if ($can ?? $user->can('super'))
                    {{ $title ?? __("Create a {$resource}") }}
                @else
                    {{ __('No ' . Statamic\Support\Str::plural($resource) . ' exist yet') }}
                @endif
            </h1>

            @if ($description ?? false)
            <p class="text-grey-70 leading-normal mb-4 text-lg antialiased">
                {{ __($description) }}
            </p>
            @endif

            <a href="{{ $route }}" class="btn-primary btn-lg">{{ __("Create {$resource}") }}</a>
        </div>
        <div class="hidden md:block w-1/2 pl-6">
            @svg($svg ?? 'empty/content')
        </div>
    </div>
    @if ($docs_link ?? false)
        <div class="flex justify-center text-center mt-8">
            <div class="bg-white rounded-full px-3 py-1 shadow-sm text-sm text-grey-70">{{ __('Learn more about') }} <a href="{{ $docs_link }}" class="text-blue hover:text-blue-dark">{{ Statamic\Support\Str::plural($resource) }}</a>.</div>
        </div>
    @endif
</div>
