<script setup lang="ts">
import { Button, Description, Input, Table, TableCell, TableColumn, TableColumns, TableRow, TableRows } from '@ui';
import { computed } from 'vue';
import { ColorVariableName, Theme, ThemeColors } from './types';
import colors from './colors';
import Preview from './Preview.vue';
import { getDefaultTheme } from '@/components/themes/utils';
import { translate as __ } from '@/translations/translator';
import ColorPicker from '@/components/themes/color-picker/ColorPicker.vue';
import Share from '@/components/themes/Share.vue';

const props = defineProps<{
    modelValue?: Theme;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', theme: Theme): void;
    (e: 'shared'): void;
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

function updateColor(colorName: ColorVariableName, value: string, isDark: boolean = false) {
    const colors = { ...theme.value.colors };
    const darkColors = { ...theme.value.darkColors };

    if (isDark) {
        darkColors[colorName] = value;
    } else {
        colors[colorName] = value;
    }

    updateColors(colors, darkColors);
}

function hasDarkColor(colorName: ColorVariableName): boolean {
    return Boolean(props.modelValue?.darkColors?.[colorName]);
}

function clearDarkColor(colorName: ColorVariableName) {
    const { [colorName]: _, ...darkColors } = theme.value.darkColors;
    updateColors(theme.value.colors, darkColors);
}

function addDarkColor(colorName: ColorVariableName) {
    updateColors(theme.value.colors, {
        ...theme.value.darkColors, [colorName]: theme.value.colors[colorName]
    });
}

function updateColors(colors: ThemeColors, darkColors: ThemeColors) {
    emit('update:modelValue', {
        id: 'custom',
        name: 'Custom',
        colors,
        darkColors
    });
}

const sharable = computed(() => theme.value.id === 'custom');
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
                            <ColorPicker
                                :model-value="theme.colors[color.name]"
                                @update:model-value="updateColor(color.name, $event, false)"
                            />

                        </TableCell>
                        <TableCell>
                            <div class="flex items-center">
                                <ColorPicker
                                    v-if="hasDarkColor(color.name)"
                                    :model-value="theme.darkColors[color.name]"
                                    @update:model-value="updateColor(color.name, $event, true)"
                                />
                                <Button
                                    v-if="hasDarkColor(color.name)"
                                    icon="x"
                                    variant="ghost"
                                    size="sm"
                                    @click="clearDarkColor(color.name)"
                                />
                                <Button
                                    v-else
                                    icon="plus"
                                    variant="ghost"
                                    size="sm"
                                    @click="addDarkColor(color.name)"
                                />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableRows>
            </Table>
        </div>
        <div class="sticky top-0 self-start space-y-4">
            <Preview :theme="theme" appearance="light" />
            <Preview :theme="theme" appearance="dark" />

            <Share v-if="sharable" :theme="theme" @shared="emit('shared')" />
        </div>
    </div>
</template>
