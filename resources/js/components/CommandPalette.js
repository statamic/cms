import { ref } from 'vue';

const commands = ref([]);

function validate(command) {
    if (typeof command.text !== 'string') {
        console.error('You must provide a `text:` string in your command object!');
    }

    if (command.type === 'action' && typeof command.action !== 'function') {
        console.error('You must provide an `action()` function to be run with your `'+command.text+'` command!');
    }

    return command;
}

function normalize(command) {
    if (command.category === undefined) command.category = 'Actions';
    if (command.type === undefined) command.type = 'action';
    if (command.icon === undefined) command.icon = 'wand';
    if (command.when === undefined) command.when = () => true;

    return validate(command);
}

export default class CommandPalette {
    categories() {
        return Statamic.$config.get('commandPaletteCategories');
    }

    add(command) {
        commands.value.push(normalize(command));
    }

    actions() {
        return commands.value.filter(command => command.category === 'Actions');
    }

    misc() {
        return commands.value.filter(command => command.category !== 'Actions');
    }
}
