@props([
    'user',
    'class' => '',
])

@if ($user->avatar())
    <img
        src="{{ $user->avatar() }}"
        class="{{ $class ?? '' }} size-7 rounded-full [button:has(&)]:rounded-full"
        alt="{{ $user->name() }}"
    />
@else
    <div
        aria-label="{{ $user->name() }}"
        class="text-2xs {{ $class ?? '' }} flex size-7 items-center justify-center rounded-full bg-gradient-to-tr from-purple-500 to-red-600 font-medium text-white [button:has(&)]:rounded-full"
    >
        {{ $user->initials() }}
    </div>
@endif
