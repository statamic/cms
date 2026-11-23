export default Manager;
declare class Manager {
    make(): import("vue").Raw<Slug>;
    create(string: any): any;
    separatedBy(separator: any): import("vue").Raw<Slug>;
    in(language: any): import("vue").Raw<Slug>;
    async(): import("vue").Raw<Slug>;
}
import Slug from './Slug';
