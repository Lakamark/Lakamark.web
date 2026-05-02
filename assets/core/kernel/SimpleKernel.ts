import {
    AppContext,
    AppKernel,
    MountableApp
} from "../contracts";

/**
 * Kernel for applications that mount immediately without Turbo navigation.
 *
 * Responsibilities:
 * - Mount the app once when booted
 * - Destroy the app when explicitly torn down
 */
export class SimpleKernel implements AppKernel {
    constructor(
        private readonly app: MountableApp,
        private readonly context: AppContext,
    ) {}

    /**
     * Mounts the application using the provided context.
     */
    boot(): void {
        this.app.mount(this.context);
    }

    /**
     * Destroys the application.
     */
    destroy(): void {
        this.app.destroy();
    }
}