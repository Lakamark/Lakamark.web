import {
    AppContext,
    AppKernel,
    MountableApp
} from "../contracts";

/**
 * Kernel for Turbo-driven applications.
 *
 * Responsibilities:
 * - Mount the app on initial boot
 * - Re-mount the app after Turbo page loads
 * - Destroy the app before Turbo caches the current page
 * - Remove Turbo event listeners when destroyed
 *
 * This keeps Turbo lifecycle concerns outside of modules.
 */
export class TurboKernel implements AppKernel {
    private readonly handleLoad = (): void => {
        this.app.mount(this.context);
    };

    private readonly handleBeforeCache = (): void => {
        this.app.destroy();
    };

    constructor(
        private readonly app: MountableApp,
        private readonly context: AppContext,
    ) {}

    /**
     * Starts the Turbo lifecycle.
     */
    boot(): void {
        this.context.document.addEventListener('turbo:load', this.handleLoad);
        this.context.document.addEventListener(
            'turbo:before-cache',
            this.handleBeforeCache,
        );

        this.app.mount(this.context);
    }

    /**
     * Stops the Turbo lifecycle and destroys the app.
     */
    destroy(): void {
        this.context.document.removeEventListener('turbo:load', this.handleLoad);
        this.context.document.removeEventListener(
            'turbo:before-cache',
            this.handleBeforeCache,
        );

        this.app.destroy();
    }
}