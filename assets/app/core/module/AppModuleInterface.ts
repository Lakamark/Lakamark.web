import {AppRunner} from "@core/runner/AppRunner";

export interface AppModuleInterface {
    mount(runner: AppRunner): void;
    destroy(): void;
    isMounted(): boolean
}