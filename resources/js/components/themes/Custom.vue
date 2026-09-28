<script setup lang="ts">
import { Button, Description, Table, TableCell, TableColumn, TableColumns, TableRow, TableRows } from '@ui';
import { computed, ref, watch } from 'vue';
import { ColorVariableName, CompleteTheme, GrayPalette, Theme, ThemeColors } from './types';
import Preview from './Preview.vue';
import { addDefaults, colors, getDefaultTheme, grayPalettes, removeDefaults, themesDeviate } from '.';
import { translate as __ } from '@/translations/translator';
import ColorPicker from '@/components/themes/color-picker/ColorPicker.vue';
import Share from '@/components/themes/Share.vue';
import GrayPicker from './GrayPicker.vue';

const props = defineProps<{
    modelValue?: Theme;
    origin?: Theme;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', theme: Theme): void;
    (e: 'shared'): void;
}>();

const theme = computed<CompleteTheme>(() => {
    const defaultTheme = getDefaultTheme();

    if (!props.modelValue) return defaultTheme;

    return addDefaults({
        id: props.modelValue.id,
        name: props.modelValue.name,
        colors: props.modelValue.colors,
        darkColors: props.modelValue.darkColors,
    });
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

function hasLightColor(colorName: ColorVariableName): boolean {
    const defaultTheme = getDefaultTheme();
    return theme.value.colors[colorName] !== defaultTheme.colors[colorName];
}

function hasDarkColor(colorName: ColorVariableName): boolean {
    return Boolean(props.modelValue?.darkColors?.[colorName]);
}

function clearLightColor(colorName: ColorVariableName) {
    const defaultTheme = getDefaultTheme();
    const colors = { ...theme.value.colors, [colorName]: defaultTheme.colors[colorName] };
    updateColors(colors, theme.value.darkColors);
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
    emit('update:modelValue', removeDefaults({
        id: 'custom',
        name: 'Custom',
        colors,
        darkColors
    }));
}

const sharable = computed(() => theme.value.id === 'custom'
    && themesDeviate(theme.value, props.origin ?? getDefaultTheme())
);

const manuallyToggledIndividualGrays = ref(false);
const grayColors = computed(() => colors.filter(c => c.name.startsWith('gray-')));
const nonGrayColors = computed(() => colors.filter(c => !c.name.startsWith('gray-')));

const selectedLightGrayPalette = computed(() => {
    for (const [paletteName, palette] of Object.entries(grayPalettes)) {
        const allMatch = Object.entries(palette).every(([shade, colorValue]) => {
            return theme.value.colors[`gray-${shade}` as ColorVariableName] === colorValue;
        });

        if (allMatch) {
            return paletteName as GrayPalette;
        }
    }

    return null;
});

const selectedDarkGrayPalette = computed(() => {
    for (const [paletteName, palette] of Object.entries(grayPalettes)) {
        const allMatch = Object.entries(palette).every(([shade, colorValue]) => {
            return theme.value.darkColors[`gray-${shade}` as ColorVariableName] === colorValue;
        });

        if (allMatch) {
            return paletteName as GrayPalette;
        }
    }

    return null;
});

const customizingIndividualGrays = computed({
    get: () => {
        if (manuallyToggledIndividualGrays.value) return true;
        return selectedLightGrayPalette.value === null && selectedDarkGrayPalette.value === null;
    },
    set: (value: boolean) => {
        manuallyToggledIndividualGrays.value = value;
    }
});

watch([selectedLightGrayPalette, selectedDarkGrayPalette], () => {
    manuallyToggledIndividualGrays.value = false;
});

function applyPaletteToColors(colors: ThemeColors, paletteName: GrayPalette): ThemeColors {
    const newColors = { ...colors };
    const palette = grayPalettes[paletteName];

    if (paletteName === 'zinc') {
        Object.keys(palette).forEach((shade) => delete newColors[`gray-${shade}`]);
    } else {
        Object.entries(palette).forEach(([shade, colorValue]) => newColors[`gray-${shade}`] = colorValue);
    }

    return newColors;
}

function applyLightGrayPalette(paletteName: GrayPalette | null) {
    paletteName ??= 'zinc';
    const newColors = applyPaletteToColors(theme.value.colors, paletteName);
    updateColors(newColors, theme.value.darkColors);
    manuallyToggledIndividualGrays.value = false;
}

function applyDarkGrayPalette(paletteName: GrayPalette | null) {
    paletteName ??= 'zinc';
    const newDarkColors = applyPaletteToColors(theme.value.darkColors, paletteName);
    updateColors(theme.value.colors, newDarkColors);
    manuallyToggledIndividualGrays.value = false;
}

function findGrayPaletteByColor(colorValue: string): GrayPalette {
    for (const [paletteName, palette] of Object.entries(grayPalettes)) {
        if (Object.values(palette).some(v => v === colorValue)) {
            return paletteName as GrayPalette;
        }
    }
    return 'zinc';
}

function collapseGrays() {
    // When collapsing, we need to consolidate any individual gray colors into a single palette
    // so we will arbitrarily pick the palette based on the top-most swatch, which is gray-50.
    const lightPalette = findGrayPaletteByColor(theme.value.colors['gray-50']);
    const darkPalette = findGrayPaletteByColor(theme.value.darkColors['gray-50']);

    const newColors = applyPaletteToColors(theme.value.colors, lightPalette);
    const newDarkColors = applyPaletteToColors(theme.value.darkColors, darkPalette);

    updateColors(newColors, newDarkColors);
    manuallyToggledIndividualGrays.value = false;
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
                    <TableRow v-for="color in nonGrayColors" :key="color.name">
                        <TableCell>
                            <Description :text="color.label" class="flex-1" />
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center w-16">
                                <ColorPicker
                                    :model-value="theme.colors[color.name]"
                                    @update:model-value="updateColor(color.name, $event, false)"
                                />
                                <Button
                                    v-if="hasLightColor(color.name)"
                                    icon="x"
                                    variant="ghost"
                                    size="sm"
                                    @click="clearLightColor(color.name)"
                                />
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center w-16">
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

                    <TableRow v-if="!customizingIndividualGrays">
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <Description text="Grays" />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    icon="arrow-down"
                                    @click="customizingIndividualGrays = true"
                                />
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center">
                                <GrayPicker
                                    :model-value="selectedLightGrayPalette"
                                    @update:model-value="applyLightGrayPalette"
                                />
                                <Button
                                    v-if="selectedLightGrayPalette !== 'zinc'"
                                    icon="x"
                                    variant="ghost"
                                    size="sm"
                                    @click="applyLightGrayPalette(null)"
                                />
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center">
                                <template v-if="selectedDarkGrayPalette !== null">
                                    <GrayPicker
                                        :model-value="selectedDarkGrayPalette"
                                        @update:model-value="applyDarkGrayPalette"
                                    />
                                    <Button
                                        icon="x"
                                        variant="ghost"
                                        size="sm"
                                        @click="applyDarkGrayPalette(null)"
                                    />
                                </template>
                                <Button
                                    v-else
                                    icon="plus"
                                    variant="ghost"
                                    size="sm"
                                    @click="applyDarkGrayPalette('slate')"
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <template v-else>
                        <TableRow v-for="(color, i) in grayColors" :key="color.name">
                            <TableCell>
                                <div class="flex items-center gap-4">
                                    <Description :text="color.label" />
                                    <Button
                                        v-if="i === 0"
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-up"
                                        @click="collapseGrays"
                                    />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center w-16">
                                    <ColorPicker
                                        :model-value="theme.colors[color.name]"
                                        @update:model-value="updateColor(color.name, $event, false)"
                                    />
                                    <Button
                                        v-if="hasLightColor(color.name)"
                                        icon="x"
                                        variant="ghost"
                                        size="sm"
                                        @click="clearLightColor(color.name)"
                                    />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center w-16">
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
                    </template>
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
