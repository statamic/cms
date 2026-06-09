<script setup>
import { onMounted, ref } from 'vue';

// https://codepen.io/cbolson/pen/emzegWP — JS only sets --relation; connectors are pure CSS anchor positioning
const root = ref(null);
const REL = ['-1', '0', '1'];

function updateRelations() {
    const rootEl = root.value;
    if (!rootEl) return;

    const radioLi = rootEl.querySelector('.options li:has(input:checked)');
    if (!radioLi) return;

    const radioIndex = [...radioLi.parentElement.children].indexOf(radioLi);

    rootEl.querySelectorAll('.items li').forEach((li, i) => {
        li.style.setProperty('--relation', REL[Math.sign(i - radioIndex) + 1]);
        li.dataset.relation = REL[Math.sign(i - radioIndex) + 1];
    });
}

onMounted(updateRelations);
</script>

<template>
    <!-- https://codepen.io/cbolson/pen/emzegWP -->
    <div id="linked-list" ref="root" class="linked-list" @change="updateRelations">
        <ul class="list items" aria-labelledby="items-label">
            <li>
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
            <li>
                <label for="logic-tree-item-3">
                    <input id="logic-tree-item-3" type="checkbox" class="cb" value="3" checked>
                    Item 3
                </label>
            </li>
            <li>
                <label for="logic-tree-item-4">
                    <input id="logic-tree-item-4" type="checkbox" class="cb" value="4" checked>
                    Item 4
                </label>
            </li>
        </ul>

        <ul class="list options" role="radiogroup" aria-labelledby="visit-label">
            <li>
                <label for="logic-tree-radio-1">
                    <input id="logic-tree-radio-1" type="radio" name="logic-tree-visit" class="rb" value="1">
                    Option 1
                </label>
            </li>
            <li>
                <label for="logic-tree-radio-2">
                    <input id="logic-tree-radio-2" type="radio" name="logic-tree-visit" class="rb" value="2">
                    Option 2
                </label>
            </li>
            <li>
                <label for="logic-tree-radio-3">
                    <input id="logic-tree-radio-3" type="radio" name="logic-tree-visit" class="rb" value="3">
                    Option 3
                </label>
            </li>
            <li>
                <label for="logic-tree-radio-4">
                    <input id="logic-tree-radio-4" type="radio" name="logic-tree-visit" class="rb" value="4">
                    Option 4
                </label>
            </li>
            <li>
                <label for="logic-tree-radio-5">
                    <input id="logic-tree-radio-5" type="radio" name="logic-tree-visit" class="rb" value="5">
                    Option 5
                </label>
            </li>
            <li>
                <label for="logic-tree-radio-6">
                    <input id="logic-tree-radio-6" type="radio" name="logic-tree-visit" class="rb" value="6" checked>
                    Option 6
                </label>
            </li>
            <li>
                <label for="logic-tree-radio-7">
                    <input id="logic-tree-radio-7" type="radio" name="logic-tree-visit" class="rb" value="7">
                    Option 7
                </label>
            </li>
        </ul>
    </div>
</template>
