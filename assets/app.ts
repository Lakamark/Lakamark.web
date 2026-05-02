import "./css/app.scss";
import '@hotwired/turbo';

import {
    AppContext,
    AppRunner
} from "./core";
import {TurboKernel} from "./core/kernel";
import {loadConfig} from "./dom";
import {createApp} from "./application";

const app: AppRunner = createApp();

const context: AppContext = {
    config: loadConfig(),
    document,
    window
}

const kernel = new TurboKernel(app, context);

/**
 * Ensures a single kernel instance is active.
 *
 * Prevents double boot by destroying any existing kernel
 * before assigning and booting the new one.
 */
window.__lmkKernel?.destroy();

window.__lmkKernel = kernel;

// Start the kernel
kernel.boot();