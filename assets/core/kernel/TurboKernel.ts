import {
    AppKernel,
    MountableApp
} from "../contracts";
import {AppContextFactory} from "../context";

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
    private mounted: boolean = false;

    constructor(
        private readonly app: MountableApp,
        private readonly buildContext: AppContextFactory,
    ) {}

    private readonly handleLoad = (): void => {
        this.mountApp();
    };

    private readonly handleBeforeCache = (): void => {
        this.unmountApp();
    };

    /**
     * Starts the Turbo lifecycle.
     */
    boot(): void {
        document.addEventListener('turbo:load', this.handleLoad);
        document.addEventListener('turbo:before-cache', this.handleBeforeCache);
    }

    /**
     * Stops the Turbo lifecycle and destroys the app.
     */
    destroy(): void {
        document.removeEventListener('turbo:load', this.handleLoad);
        document.removeEventListener('turbo:before-cache', this.handleBeforeCache);

        this.unmountApp();
    }

    private mountApp(): void {
        if (this.mounted) {
            this.unmountApp();
        }

        this.app.mount(this.buildContext());
        this.mounted = true;
    }

    private unmountApp(): void {
        if (!this.mounted) {
            return;
        }

        this.app.destroy();
        this.mounted = false;
    }
}