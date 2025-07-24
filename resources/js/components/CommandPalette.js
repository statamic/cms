import { ref } from 'vue';

const commands = ref({});

export default class CommandPalette {
    categories() {
        return Statamic.$config.get('commandPaletteCategories');
    }

    add(command) {
        commands.value.push(command);
    }

    get() {
        return commands.value;
    }
}
