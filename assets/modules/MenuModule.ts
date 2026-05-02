import {
    AbstractModule,
    AppContext,
    MODULE_NAMES
} from "../core";
import {queryOptional} from "../dom";

const HEADER_SELECTOR = '#main-header';
const MENU_BUTTON_SELECTOR = '#js-hamburger';
const MENU_SELECTOR = '#menuNav';
const MENU_OPEN_CLASS = 'is-menu-open';

/**
 * Manages the main navigation menu state.
 *
 * Responsibilities:
 * - Toggles the header `is-menu-open` class
 * - Updates hamburger `aria-expanded`
 * - Closes the menu on destroy
 *
 * CSS contract:
 * ```css
 * .header.is-menu-open { ... }
 * ```
 */
export class MenuModule extends AbstractModule {
    readonly name = MODULE_NAMES.MENU;

    private header: HTMLElement | null = null;
    private button: HTMLButtonElement | null = null;
    private menu: HTMLElement | null = null;
    private isOpen = false;

    protected onMount(_context: AppContext): void {
        this.header = queryOptional<HTMLElement>(document, HEADER_SELECTOR);
        this.button = queryOptional<HTMLButtonElement>(document, MENU_BUTTON_SELECTOR);
        this.menu = queryOptional<HTMLElement>(document, MENU_SELECTOR);

        if (!this.header || !this.button || !this.menu) {
            return;
        }

        this.button.addEventListener('click', this.handleToggle);
        this.close();
    }

    protected onDestroy(): void {
        this.button?.removeEventListener('click', this.handleToggle);
        this.close();

        this.header = null;
        this.button = null;
        this.menu = null;
        this.isOpen = false;
    }

    private handleToggle = (): void => {
        this.isOpen ? this.close() : this.open();
    };

    private open(): void {
        this.isOpen = true;

        this.header?.classList.add(MENU_OPEN_CLASS);
        this.button?.setAttribute('aria-expanded', 'true');
    }

    private close(): void {
        this.isOpen = false;

        this.header?.classList.remove(MENU_OPEN_CLASS);
        this.button?.setAttribute('aria-expanded', 'false');
    }
}