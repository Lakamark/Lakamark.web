import "./css/app.scss";
import '@hotwired/turbo';

import {
    AppRunner,
    buildAppContext
} from "./core";
import {
    attachKernelToWindow,
    TurboKernel
} from "./core/kernel";
import {
    DebugModule,
    HeaderModule,
    MenuModule,
    ThemeModule
} from "./modules";

import {
    registerCustomElements
} from "./dom";
import {createApp} from "./application";

// Register custom elements BEFORE creating/mounting the application.
registerCustomElements();

const app: AppRunner = createApp();


// Load modules
app
    .register(new ThemeModule())
    .register(new HeaderModule())
    .register(new MenuModule())
;

// In development-like environments, register debug modules.
if (buildAppContext().config.environment !== 'prod') {
    app.register(new DebugModule());
}

// Init the kernel (We use the Turbo script),
// we created an optimized kernel to run on Turbo.
const kernel = new TurboKernel(app, buildAppContext);

// Ensure a single global kernel instance
attachKernelToWindow(kernel);

// Start the kernel
kernel.boot();