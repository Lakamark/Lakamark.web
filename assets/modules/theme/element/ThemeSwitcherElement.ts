import {ThemeManagerContract} from "../Contracts";

/**
 * Custom element responsible for user theme switching.
 *
 * This element does not own theme logic.
 * It delegates all theme behavior to ThemeManager.
 */
export class ThemeSwitcherElement extends HTMLElement {
    private manager: ThemeManagerContract | null = null;
    private button: HTMLButtonElement | null = null;

    connectedCallback(): void {
        this.button = this.querySelector<HTMLButtonElement>('button');

        if (!this.button) {
            return;
        }

        this.button.addEventListener('click', this.handleClick);
        this.syncState();
    }

    disconnectedCallback(): void {
        this.button?.removeEventListener('click', this.handleClick);
        this.button = null;
    }

    /**
     * Inject the theme manager used by this element.
     */
    setThemeManager(manager: ThemeManagerContract): void {
        this.manager = manager;
        this.syncState();
    }

    private handleClick = (): void => {
        this.manager?.toggle();
        this.syncState();
    };

    private syncState(): void {
        if (!this.manager || !this.button) {
            return;
        }

        const theme = this.manager.getCurrentTheme();
        this.button.setAttribute(
            'aria-label',
            theme === 'night-theme'
                ? 'Switch to day theme'
                : 'Switch to night theme',
        );

        this.button.setAttribute(
            'aria-pressed',
            theme === 'night-theme' ? 'true' : 'false',
        );
    }
}