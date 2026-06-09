<template>
    <div class="linked-list">
        <ul>
            <li class="linked-list__page-label" style="anchor-name: --page-1;">
                {{ __('Page :number', { number: 1 }) }}
            </li>
            <li class="linked-list__connector" style="--end-connection: --page-2;">
                Item 1
            </li>
            <li class="linked-list__connector linked-list__page-leap" style="--end-connection: --page-3;">
                <div class="linked-list__extra-leap-connector"></div>
                Item 2
            </li>
            <li class="linked-list__connector linked-list__page-leap" style="--end-connection: --page-3;">
                <div class="linked-list__extra-leap-connector"></div>
                Item 3
            </li>
            <li class="linked-list__connector" style="--end-connection: --page-2;">
                Item 4
            </li>
        </ul>
        
        <ul>
            <li class="linked-list__page-label" style="anchor-name: --page-2;">
                {{ __('Page :number', { number: 2 }) }}
            </li>
            <li>
                Item 1
            </li>
            <li>
                Item 2
            </li>
            <li>
                Item 3
            </li>
            <li>
                Item 4
            </li>
            <li>
                Item 5
            </li>
            <li>
                Item 6
            </li>
            <li>
                Item 7
            </li>
        </ul>

        <ul>
            <li class="linked-list__page-label" style="anchor-name: --page-3;">
                {{ __('Page :number', { number: 3 }) }}
            </li>
            <li>
                Item 1
            </li>
            <li>
                Item 2
            </li>
            <li>
                Item 3
            </li>
            <li>
                Item 4
            </li>
            <li>
                Item 5
            </li>
            <li>
                Item 6
            </li>
            <li>
                Item 7
            </li>
        </ul>
    </div>
</template>
