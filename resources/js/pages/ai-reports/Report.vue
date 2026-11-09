<script setup>
import { computed, ref, watch } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, ButtonGroup, Panel, PanelHeader, Heading, Card, Table, TableRows, TableRow, TableCell, Checkbox, Icon } from '@ui';
import { Link } from '@inertiajs/vue3';
import useAiChat from '@/composables/use-ai-chat.js';

// Prototype (phase 2): one run of a scheduled prompt, like a form submission: what it found, grouped, each on the page it's on (see AiReportsController)
const props = defineProps(['promptTitle', 'instructions', 'date', 'summary', 'sections', 'listUrl', 'newerUrl', 'olderUrl']);

const found = computed(() => props.sections.reduce((total, section) => total + section.items.length, 0));

// Ticked off as you fix them. Prototype: remembered in this browser for this run, where a real one would save them with the report.
// Each run has its own, so stepping to an older one loads its ticks
const ticksKey = computed(() => `ai-report-ticks-${props.promptTitle}-${props.date}`);
const ticks = ref({});
watch(
    ticksKey,
    (key) => {
        try {
            ticks.value = JSON.parse(localStorage.getItem(key)) ?? {};
        } catch {
            ticks.value = {};
        }
    },
    { immediate: true },
);
function tick(id, value) {
    ticks.value = { ...ticks.value, [id]: value };
    try {
        localStorage.setItem(ticksKey.value, JSON.stringify(ticks.value));
    } catch {}
}
const done = computed(() => Object.values(ticks.value).filter(Boolean).length);

// What the AI would write as an overview, in short points, by what matters most rather than by page: one for each important kind of problem
// and why it matters, then the housekeeping ones together
const phrase = (section) => `${section.items.length} ${section.label ?? section.heading.toLowerCase()}`;
const points = computed(() => {
    const withItems = props.sections.filter((section) => section.items.length);
    const important = withItems.filter((section) => !section.minor).map((section) => `${phrase(section)}. ${section.why}`);
    const minor = withItems.filter((section) => section.minor).map(phrase);
    return [
        ...important,
        ...(minor.length ? [`When there’s time: ${minor.length > 1 ? `${minor.slice(0, -1).join(', ')} and ${minor.at(-1)}` : minor[0]}.`] : []),
    ];
});
// Opens the AI chat and asks it to fix the section's problems, as you would by typing the request
const { demo } = useAiChat();
const fix = (section) => demo(section.fix);
const percent = computed(() => (found.value ? Math.round((done.value / found.value) * 100) : 0));
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[date, promptTitle, __('AI Reports')]" />

        <Header :title="date" icon="pulse">
            <ButtonGroup>
                <Button size="sm" :href="newerUrl" :disabled="!newerUrl" icon="chevron-left" :aria-label="__('Newer')" v-tooltip="__('Newer')" />
                <Button size="sm" :href="olderUrl" :disabled="!olderUrl" icon="chevron-right" :aria-label="__('Older')" v-tooltip="__('Older')" />
            </ButtonGroup>
        </Header>

        <!-- The headline and the overview the AI writes, with the ring beside them -->
        <Panel>
            <!-- The headline in a header row like the lists' below, with the read-only note on the right where theirs have their actions -->
            <PanelHeader class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <Heading :text="summary" />
                <!-- That the AI only found these, and changed nothing -->
                <span class="ms-auto flex items-center gap-1.5 text-xs text-gray-500"><Icon name="eye" class="size-3.5" />{{ __('The AI found these but hasn’t changed anything without asking.') }}</span>
            </PanelHeader>
            <Card>
                <div class="flex items-center gap-6">
                    <ul v-if="points.length" class="flex-1 list-disc space-y-1 ps-4 text-sm/6 text-gray-700 dark:text-gray-300">
                        <li v-for="point in points" :key="point">{{ point }}</li>
                    </ul>

                    <!-- How many have been ticked off, as a ring on the right -->
                    <div v-if="found" class="relative grid size-20 shrink-0 place-items-center">
                        <svg viewBox="0 0 36 36" class="absolute inset-0 size-full -rotate-90">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3" class="stroke-gray-200 dark:stroke-gray-700" />
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3" stroke-linecap="round" pathLength="100" :stroke-dasharray="`${percent} 100`" class="stroke-green-500 transition-[stroke-dasharray] duration-300" />
                        </svg>
                        <div class="text-center leading-tight tabular-nums">
                            <div class="text-base font-medium">{{ done }}/{{ found }}</div>
                            <div class="text-[10px] text-gray-500">{{ __('done') }}</div>
                        </div>
                    </div>
                </div>
            </Card>
        </Panel>

        <!-- A panel for each kind of problem, each row ticked off once it's fixed -->
        <Panel v-for="section in sections" :key="section.heading" :heading="`${section.heading} · ${section.items.length}`">
            <!-- How to use the tick boxes, on the right of each list's header. Kinds the AI can fix also get a way to ask it to. -->
            <template #header-actions>
                <Button v-if="section.fix" size="xs" icon="ai-edit" :text="__('Fix with AI')" @click="fix(section)" />
                <!-- A panel header with a button in it drops its right padding to 4px (for a button at the end), so the note puts the other 14px back -->
                <span class="text-xs text-gray-500" :class="{ 'pe-3.5': section.fix }">{{ section.fix ? __('or tick each one off as you fix it') : __('Tick each one off as you fix it') }}</span>
            </template>
            <Card class="py-0!">
                <Table>
                    <TableRows>
                        <TableRow v-for="(item, i) in section.items" :key="i" :class="{ 'opacity-50': ticks[`${section.heading}-${i}`] }">
                            <!-- As narrow as the box, so the page's name sits 12px from it (the next cell's padding), like a tick box and its label -->
                            <TableCell class="w-px pe-0">
                                <Checkbox solo :label="__('Fixed')" :model-value="!!ticks[`${section.heading}-${i}`]" @update:model-value="tick(`${section.heading}-${i}`, $event)" />
                            </TableCell>
                            <TableCell class="w-1/3 font-medium" :class="{ 'line-through': ticks[`${section.heading}-${i}`] }">
                                <Link v-if="item.url" :href="item.url">{{ item.page }}</Link>
                                <template v-else>{{ item.page }}</template>
                            </TableCell>
                            <TableCell class="text-gray-600 dark:text-gray-400">{{ item.detail }}</TableCell>
                        </TableRow>
                    </TableRows>
                </Table>
            </Card>
        </Panel>

    </div>
</template>
