import {AppContext} from "./AppContext";

/**
 * Minimal contract required by kernels to control the app lifecycle.
 */
export interface MountableApp {
    mount(context: AppContext): void;
    destroy(): void;
}