<template>
    <div class="linked-list">
        <ul class="items">
            <li class="linked-list__page-label">
                {{ __('Page :number', { number: 1 }) }}
            </li>
            <li class="linked-list__linked" style="--destination: --page-2;">
                Item 1
            </li>
            <li>
                Item 2
            </li>
            <li class="linked-list__linked" style="--destination: --page-2;">
                Item 3
            </li>
            <li class="linked-list__linked" style="--destination: --page-2;">
                Item 4
            </li>
        </ul>
        
        <ul class="options" role="radiogroup" :aria-label="__('Destination page')">
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
    </div>
</template>
