@props(['user', 'class' => ''])

@if ($user->avatar())
    <img src="{{ $user->avatar() }}" class="size-7 rounded-full [button:has(&)]:rounded-full {{ $class ?? '' }}" alt="{{ $user->name() }}" />
@else
    <div aria-label="{{ $user->name() }}" class="size-7 text-white text-2xs font-medium flex items-center justify-center rounded-full [button:has(&)]:rounded-full bg-gradient-to-tr from-purple-500 to-red-600 {{ $class ?? '' }}">{{ $user->initials() }}</div>
@endif
