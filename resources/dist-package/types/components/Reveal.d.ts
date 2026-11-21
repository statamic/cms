export default Reveal;
declare class Reveal {
    use(ref: any, callback: any): void;
    mount(el: any, callback: any): void;
    element(el: any): void;
    focusableControl(root: any): any;
    isVisible(control: any): boolean;
    invalid(): void;
}
