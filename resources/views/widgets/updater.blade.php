@php
    use function Statamic\trans as __;
@endphp

<div class="card h-full p-0">
    <header class="flex items-center justify-between border-b p-4 dark:border-b dark:border-dark-900 dark:bg-dark-650">
        <h2 class="flex items-center">
            <div class="h-6 w-6 text-gray-800 dark:text-dark-200 ltr:mr-2 rtl:ml-2">
                @cp_svg('icons/light/loading-bar')
            </div>
            <span>{{ __('Updates') }}</span>
        </h2>
        @if ($count)
            <a href="{{ cp_route('updater') }}" class="badge-sm bg-green-600 text-white">
                {{ trans_choice('1 update available|:count updates available', $count) }}
            </a>
        @endif
    </header>
    <section class="px-4 py-2">
        @if (! $count)
            <p class="text-center text-base text-gray-700">{{ __('Everything is up to date.') }}</p>
        @endif

        @if ($hasStatamicUpdate)
            <div class="flex items-center justify-between text-sm">
                <a href="{{ cp_route('updater.product', 'statamic') }}" class="py-1 font-bold hover:text-blue">
                    Statamic Core
                </a>
            </div>
        @endif

        @foreach ($updatableAddons as $slug => $addon)
            <div class="flex w-full items-center justify-between text-sm">
                <a href="{{ cp_route('updater.product', $slug) }}" class="py-1 hover:text-blue">{{ $addon }}</a>
            </div>
        @endforeach
    </section>
</div>
