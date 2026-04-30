import {ThemeManagerContract} from "../Contracts";

/**
 * Custom element responsible for user theme switching.
 *
 * This element does not own theme logic.
 * It delegates all theme behavior to ThemeManager.
 */
export class ThemeSwitcherElement extends HTMLElement {
    private manager: ThemeManagerContract | null = null;

    connectedCallback(): void {
        this.setAttribute('role', 'button');
        this.setAttribute('tabindex', '0');

        this.addEventListener('click', this.handleClick);
        this.addEventListener('keydown', this.handleKeydown);

        this.syncState();
    }

    disconnectedCallback(): void {
        this.removeEventListener('click', this.handleClick);
        this.removeEventListener('keydown', this.handleKeydown);
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

    private handleKeydown = (event: KeyboardEvent): void => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        event.preventDefault();

        this.manager?.toggle();
        this.syncState();
    };

    private syncState(): void {
        if (!this.manager) {
            return;
        }

        const theme = this.manager.getCurrentTheme();
        this.setAttribute(
            'aria-pressed',
            theme === 'night-theme' ? 'true' : 'false',
        );

        this.setAttribute(
            'aria-label',
            theme === 'night-theme'
                ? 'Switch to day theme'
                : 'Switch to night theme',
        );
    }
}