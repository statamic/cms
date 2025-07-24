import { uniqueId } from 'lodash-es';
import { ref } from 'vue';
import uniqid from 'uniqid';

const commands = ref({});

class Command {
    constructor(command) {
        this.key = uniqid();
        this.category = command.category ?? 'Contextual Actions';
        this.type = command.type ?? 'action';
        this.icon = command.icon ?? 'wand';
        this.when = command.when ?? (() => true);
        this.text = command.text;
        this.url = command.url;
        this.action = command.action;
        this.prioritize = command.prioritize ?? false;

        this.#validate();
    }

    remove() {
        delete commands.value[this.key];
    }

    #validate() {
        if (typeof this.text !== 'string') {
            console.error('You must provide a `text:` string in your command object!');
        }

        if (this.type === 'action' && typeof this.action !== 'function') {
            console.error('You must provide an `action()` function to be run with your `'+this.text+'` command!');
        }
    }
}

export default class CommandPalette {
    categories() {
        return Statamic.$config.get('commandPaletteCategories');
    }

    add(command) {
        command = new Command(command);

        commands.value[command.key] = command;

        return command;
    }

    actions() {
        return Object.values(commands.value).filter(command => command.category === 'Contextual Actions');
    }

    misc() {
        return Object.values(commands.value).filter(command => command.category !== 'Contextual Actions');
    }
}
