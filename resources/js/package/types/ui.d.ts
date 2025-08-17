declare module '@statamic/cms/ui' {
    import { DefineComponent } from 'vue';

    export interface ButtonProps {
        variant?: 'primary' | 'secondary' | 'danger';
        size? 'sm' | 'md' | 'lg';
        text: string;
    }

    export const ExampleButton: DefineComponent<ButtonProps>;
}
