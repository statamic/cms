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

<style>
/* https://codepen.io/cbolson/pen/emzegWP — flat CSS equivalent (Vue SFC can't use nested @container) */
.linked-list {
    --checkbox-checked-border-color: dodgerblue;
    --radio-checked-border-color: dodgerblue;
    --join-stroke: 1px;
    --join-line: var(--join-stroke) dashed dodgerblue;
    --join-radius: 20px;
    --gap: 10vw;
    --clr-lines: rgba(0 0 0 / 0.25);

    position: relative;
    display: grid;
    align-items: start;
    grid-template-columns: 1fr 1fr;
    width: min(100%, 800px);
    gap: var(--gap);
}

:where(.dark) .linked-list {
    --clr-lines: rgba(255 255 255 / 0.25);
}

.linked-list .list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 0.5rem;
}

.linked-list .list > li {
    border: 1px solid var(--clr-lines);
    display: flex;
    align-items: center;
}

.linked-list .list > li > label {
    flex: 1;
    padding: 0.5rem;
    cursor: pointer;
}

.linked-list .list.items li:has(:checked) label {
    anchor-name: --checked-option;
    anchor-scope: --checked-option;
    border-color: var(--checkbox-checked-border-color);
}

.linked-list .list.items li:has(:checked) label::before,
.linked-list .list.items li:has(:checked) label::after {
    content: '';
    position: absolute;
    pointer-events: none;
    border: var(--join-line);
    right: calc(anchor(left --radio-option) + var(--gap) / 2);
    left: anchor(right --checked-option);
}

.linked-list .list.items li:has(:checked) label::after {
    right: anchor(left --radio-option);
    left: calc(anchor(left --radio-option) - var(--gap) / 2 - var(--join-stroke));
}

/* source above target */
.linked-list .list.items li[data-relation="-1"]:has(:checked) label::before {
    border-left-color: transparent;
    border-bottom-color: transparent;
    border-radius: 0 var(--join-radius) 0 0;
    top: anchor(center --checked-option);
    bottom: anchor(top --radio-option);
}

.linked-list .list.items li[data-relation="-1"]:has(:checked) label::after {
    border-right-color: transparent;
    border-top-color: transparent;
    border-radius: 0 0 0 var(--join-radius);
    top: calc(anchor(top --radio-option) - var(--join-stroke));
    height: var(--join-radius);
    bottom: auto;
}

/* source below target */
.linked-list .list.items li[data-relation="1"]:has(:checked) label::before {
    border-left-color: transparent;
    border-top-color: transparent;
    border-radius: 0 0 var(--join-radius) 0;
    top: anchor(bottom --radio-option);
    bottom: anchor(center --checked-option);
}

.linked-list .list.items li[data-relation="1"]:has(:checked) label::after {
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-radius: var(--join-radius) 0 0 0;
    top: auto;
    bottom: calc(anchor(bottom --radio-option) - var(--join-stroke));
    height: var(--join-radius);
}

/* source level with target */
.linked-list .list.items li[data-relation="0"]:has(:checked) label::before {
    border-top-color: transparent;
    border-right-color: transparent;
    border-left-color: transparent;
    border-radius: 0;
    top: calc(anchor(center --radio-option) - var(--join-stroke));
    bottom: calc(anchor(center --checked-option) + var(--join-stroke));
    right: anchor(left --radio-option);
    left: anchor(right --checked-option);
}

.linked-list .list.items li[data-relation="0"]:has(:checked) label::after {
    display: none;
}

.linked-list .list.options li:has(:checked) {
    anchor-name: --radio-option;
    border-color: var(--radio-checked-border-color);
}
</style>
