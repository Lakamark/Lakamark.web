import {AbstractModule} from "./AbstractModule";

export class AppRunner {
    constructor(
        private modules: AbstractModule[]
    ) {
    }

    mount(): void {
        this.modules.forEach((module: AbstractModule): void => module.mount());
    }

    destroy(): void {
        [...this.modules].reverse().forEach((module): void => module.destroy());
    }
}