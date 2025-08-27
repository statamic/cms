import { spawn } from 'child_process';


export default function() {
    return {
        name: 'statamic-prevent-server',

        config(config, { command }) {
            if (command === 'serve' && !process.env.STATAMIC_FORCE_SERVE) {
                console.log('\x1b[33m[Statamic] Vite dev server current not supported. Automatically running "vite build --watch" instead...\x1b[0m');
                console.log('\x1b[90m[Statamic] Use STATAMIC_FORCE_SERVE=1 to bypass this behavior.\x1b[0m');

                const child = spawn('npx', ['vite', 'build', '--watch'], {
                    stdio: 'inherit',
                    cwd: process.cwd()
                });

                child.on('error', (err) => {
                    console.error('Failed to start vite build --watch:', err);
                    process.exit(1);
                });

                process.exit(0);
            }
        }
    };
}
