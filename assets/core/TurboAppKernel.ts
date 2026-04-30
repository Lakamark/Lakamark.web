import {AppRunner} from "./AppRunner";
import {AppConfig, readAppConfig} from "../dom";

/**
 * Bridges Turbo lifecycle events with the application runner.
 *
 * Ensures:
 * - one-time event registration
 * - safe mount on initial load
 * - destroy before Turbo caches the page
 */
export class TurboAppKernel {
    private booted: boolean = false;

    constructor(
        private readonly runner: AppRunner,
    ) {}

    boot(): void {
        if (this.booted) {
            return;
        }

        this.booted = true;

        document.addEventListener('turbo:load', this.mount);
        document.addEventListener('turbo:before-cache', this.destroy);
        document.addEventListener('turbo:before-render', this.destroy);
    }

    public destroy: () => void = (): void => {
        this.runner.destroy();
    };

    private mount: () => void = (): void => {
        const config: AppConfig = readAppConfig();

        this.runner.mount(config);
    }

}