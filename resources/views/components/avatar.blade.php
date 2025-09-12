@props(['user', 'class' => ''])

@if ($user->avatar())
    <img src="{{ $user->avatar() }}" class="size-7 rounded-xl [button:has(&)]:rounded-xl shape-squircle {{ $class ?? '' }}" alt="{{ $user->name() }}" />
@else
    <div aria-label="{{ $user->name() }}" class="size-7 text-white text-2xs font-medium flex items-center justify-center rounded-xl [button:has(&)]:rounded-xl bg-gradient-to-tr from-purple-500 to-red-600 shape-squircle {{ $class ?? '' }}">{{ $user->initials() }}</div>
@endif
