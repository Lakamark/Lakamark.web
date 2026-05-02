import {AppRunner} from "../core";
import {AppConfig} from "../dom/contracts";
import {assertAppConfigShape, loadConfig} from "../dom";

/**
 * Creates the frontend application instance.
 *
 * This is the composition layer of the frontend app.
 * It decides which modules are registered in the AppRunner.
 *
 * For now, the application has no feature modules yet.
 * Modules will be added here progressively.
 */
export function createApp(config?: AppConfig): AppRunner {
    const finalConfig = config ?? loadConfig();

    assertAppConfigShape(finalConfig);

    return new AppRunner();
}