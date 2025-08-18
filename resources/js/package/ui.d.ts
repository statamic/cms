declare module '@statamic/cms/ui' {
    import { DefineComponent } from 'vue';

    // Example type definitions just to check intellisense is working. We'll update later.
    export interface ButtonProps {
        variant?: 'primary' | 'secondary' | 'danger';
        size?: 'sm' | 'md' | 'lg';
        text: string;
    }
    export const Button: DefineComponent<ButtonProps>;
}
