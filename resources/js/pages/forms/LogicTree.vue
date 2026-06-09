<template>
  <div id="linked-list" ref="root" class="linked-list" @change="updateRelations">
    <ul class="items">
      <li class="linked-list__page-label">{{ __('Page :number', { number: 1 }) }}</li>
      <li style="--destination: --page-2;">
        <label for="logic-tree-item-1">
          <input id="logic-tree-item-1" type="checkbox" class="cb" value="1" checked>
          Item 1
        </label>
      </li>
      <li>
        <label for="logic-tree-item-2">
          <input id="logic-tree-item-2" type="checkbox" class="cb" value="2">
          Item 2
        </label>
      </li>
      <li style="--destination: --page-2;">
        <label for="logic-tree-item-3">
          <input id="logic-tree-item-3" type="checkbox" class="cb" value="3" checked>
          Item 3
        </label>
      </li>
      <li style="--destination: --page-2;">
        <label for="logic-tree-item-4">
          <input id="logic-tree-item-4" type="checkbox" class="cb" value="4" checked>
          Item 4
        </label>
      </li>
    </ul>

    <ul class="options" role="radiogroup" :aria-label="__('Destination page')">
      <li class="linked-list__page-label" style="anchor-name: --page-2;">
        <label for="logic-tree-page-2">
          <input id="logic-tree-page-2" type="radio" name="logic-tree-destination" class="rb" value="2" checked>
          {{ __('Page :number', { number: 2 }) }}
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-1">
          <input id="logic-tree-page-2-item-1" type="checkbox" class="cb" value="1">
          Item 1
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-2">
          <input id="logic-tree-page-2-item-2" type="checkbox" class="cb" value="2">
          Item 2
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-3">
          <input id="logic-tree-page-2-item-3" type="checkbox" class="cb" value="3">
          Item 3
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-4">
          <input id="logic-tree-page-2-item-4" type="checkbox" class="cb" value="4">
          Item 4
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-5">
          <input id="logic-tree-page-2-item-5" type="checkbox" class="cb" value="5">
          Item 5
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-6">
          <input id="logic-tree-page-2-item-6" type="checkbox" class="cb" value="6">
          Item 6
        </label>
      </li>
      <li>
        <label for="logic-tree-page-2-item-7">
          <input id="logic-tree-page-2-item-7" type="checkbox" class="cb" value="7">
          Item 7
        </label>
      </li>
    </ul>

    
  </div>
</template>
