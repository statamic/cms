<script setup>

import { Link } from '@inertiajs/vue3';
</script>

<template>

    <nav class="nav-main">
        <div>
            <ul>
                <li><Link href="/cp/dashboard">Dashboard</Link></li>
                <li><Link href="/cp/collections/articles">Articles</Link></li>
                <li><Link href="/cp/utilities/phpinfo">PHPInfo (Non-inertia)</Link></li>
            </ul>
        </div>
    </nav>


<!--    @php-->
<!--    use function Statamic\trans as __;-->
<!--    @endphp-->

<!--    @section('nav-main')-->
<!--    <nav class="nav-main">-->
<!--        @foreach ($nav as $section)-->
<!--        <div>-->
<!--            @if ($section['display'] !== 'Top Level')-->
<!--            <div class="section-title">{{ __($section['display']) }}</div>-->
<!--            @endif-->
<!--            <ul>-->
<!--                @foreach ($section['items'] as $item)-->
<!--                @unless ($item->view())-->
<!--                <li v-pre>-->
<!--                    <a @class(['active' => $item->isActive()]) href="{{ $item->url() }}" {{ $item->attributes() }}>-->
<!--                    {!! $item->svg() !!}-->
<!--                    <span>{{ __($item->name()) }}</span>-->
<!--                    </a>-->
<!--                    @if ($item->children() && $item->isActive())-->
<!--                    <ul>-->
<!--                        @foreach ($item->children() as $child)-->
<!--                        <li>-->
<!--                            <a href="{{ $child->url() }}" {{ $item->attributes() }} @class(['active' => $child->isActive()])>-->
<!--                                {{ __($child->name()) }}-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        @endforeach-->
<!--                    </ul>-->
<!--                    @endif-->
<!--                </li>-->
<!--                @else-->
<!--                @include($item->view())-->
<!--                @endunless-->
<!--                @endforeach-->
<!--            </ul>-->
<!--        </div>-->
<!--        @endforeach-->
<!--    </nav>-->
<!--    @stop-->

<!--    @yield('nav-main')-->

</template>
