import {AppConfig} from "./contracts";

/**
 * Loads the application configuration from the DOM.
 *
 * @throws Error if the config script is missing or invalid
 */
export function loadConfig(): AppConfig {
    const el: HTMLElement|null = document.getElementById('lmk-config');

    if (!el) {
        throw new Error('[AppConfig] Missing #lmk-config script');
    }

    try {
        return JSON.parse(el.textContent || '') as AppConfig;
    } catch {
        throw new Error('[AppConfig] Invalid JSON config');
    }
}