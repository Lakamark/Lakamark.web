import {ThemeSwitcherElement} from "../theme";

/**
 * Registers all custom elements used in the application.
 *
 * Must be called once at application startup.
 */
export function registerCustomElements(): void {
    if (!customElements.get('lmk-theme-switcher')) {
        customElements.define('lmk-theme-switcher', ThemeSwitcherElement);
    }
}