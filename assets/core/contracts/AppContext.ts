import {AppConfig} from "../../dom/contracts";

export interface AppContext {
    /**
     * Application configuration (from lmk-config script)
     */
    config: AppConfig;

    /**
     * DOM access (testable / injectable)
     */
    document: Document;

    /**
     * Window access (testable / injectable)
     */
    window: Window;

    // future-proof
    // services?: AppServices;
}