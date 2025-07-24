import { ref } from 'vue';

const commands = ref({});

export default class CommandPalette {
    add(command) {
        commands.value.push(command);
    }

    get() {
        return commands.value;
    }
}
