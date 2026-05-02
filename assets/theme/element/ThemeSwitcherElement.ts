import {ThemeManager} from "../core";

/**
 * Theme switcher custom element.
 *
 * Expected structure:
 *
 * <lmk-theme-switcher>
 *   <button type="button">...</button>
 * </lmk-theme-switcher>
 *
 * This element only handles UI events.
 * Theme logic is delegated to ThemeManager.
 */
export class ThemeSwitcherElement extends HTMLElement {
    private manager: ThemeManager | null = null;
    private button: HTMLButtonElement | null = null;

    /**
     * Injects the ThemeManager used by this switcher.
     */
    setThemeManager(themeManager: ThemeManager): void {
        this.manager = themeManager;
    }

    connectedCallback(): void {
        this.button = this.querySelector<HTMLButtonElement>('button');

        if (!this.button) {
            return;
        }

        this.button.addEventListener('click', this.handleClick);
    }

    disconnectedCallback(): void {
        this.button?.removeEventListener('click', this.handleClick);
        this.button = null;
    }

    /**
     * Toggles the current theme.
     */
    private handleClick = (): void => {
        this.manager?.toggle();
    };
}