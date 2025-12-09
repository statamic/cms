<script setup lang="ts">
import { Input, Description, Table, TableRows, TableRow, TableCell, TableColumns, TableColumn, Card } from '@ui';
import { computed } from 'vue';
import { PartialTheme, Theme } from './types';
import colors from './colors';
import Preview from './Preview.vue';
import { getDefaultTheme } from '@/components/themes/utils';
import { translate as __ } from '@/translations/translator';

const props = defineProps<{
    modelValue?: Theme;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', theme: PartialTheme): void;
}>();

const theme = computed<Theme>(() => {
    const defaultTheme = getDefaultTheme();

    if (!props.modelValue) return defaultTheme;

    return {
        id: props.modelValue.id,
        name: props.modelValue.name,
        colors: { ...defaultTheme.colors, ...props.modelValue.colors },
        darkColors: { ...defaultTheme.darkColors, ...props.modelValue.darkColors }
    };
});

function updateColor(colorName: string, value: string, isDark: boolean = false) {
    const colors = { ...theme.value.colors };
    const darkColors = { ...theme.value.darkColors };

    if (isDark) {
        darkColors[colorName] = value;
    } else {
        colors[colorName] = value;
    }

    const newTheme = {
        id: 'custom',
        name: 'Custom',
        colors,
        darkColors
    };

    emit('update:modelValue', newTheme);
}
</script>

<template>
    <div class="grid grid-cols-[3fr_2fr] gap-10">
        <div class="">
            <Table>
                <TableColumns>
                    <TableColumn>{{ __('Color') }}</TableColumn>
                    <TableColumn>{{ __('Light') }}</TableColumn>
                    <TableColumn>{{ __('Dark') }}</TableColumn>
                </TableColumns>
                <TableRows>
                    <TableRow v-for="color in colors" :key="color.name">
                        <TableCell>
                            <Description :text="color.label" class="flex-1" />
                        </TableCell>
                        <TableCell>
                            <Input
                                type="color"
                                size="sm"
                                :model-value="theme.colors[color.name]"
                                @update:model-value="updateColor(color.name, $event)"
                           />
                        </TableCell>
                        <TableCell>
                            <Input
                                type="color"
                                size="sm"
                                :model-value="theme.darkColors?.[color.name]"
                                @update:model-value="updateColor(color.name, $event, true)"
                            />
                        </TableCell>
                    </TableRow>
                </TableRows>
            </Table>
        </div>
        <div class="sticky top-0 self-start space-y-4">
            <Preview :theme="theme" appearance="light" />
            <Preview :theme="theme" appearance="dark" />
        </div>
    </div>
</template>
