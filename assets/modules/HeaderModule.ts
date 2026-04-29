import {AbstractModule} from "../core";
import {AppConfig, queryOptional} from "../dom";

const HEADER_SELECTOR = '.header';
const SCROLLED_CLASS = 'is-scrolled';
const HIDDEN_CLASS = 'is-hidden';
const MENU_OPEN_CLASS = 'is-menu-open';

/**
 * Manages header UI state based on scroll behavior.
 *
 * Responsibilities:
 * - Adds `is-scrolled` when the page is scrolled
 * - Adds `is-hidden` when scrolling down
 * - Resets all classes on destroy
 *
 * Notes:
 * - Designed to work with Turbo navigation (mount/destroy lifecycle)
 * - Does not manage menu state (`is-menu-open`) → handled by MenuModule
 * - Safe if `.header` is not present (no-op)
 *
 * CSS contract:
 * ```css
 * .header.is-scrolled { ... }
 * .header.is-hidden { ... }
 * .header.is-menu-open { ... }
 * ```
 */
export class HeaderModule extends AbstractModule {
    private header: HTMLElement | null = null;
    private lastScrollY = 0;

    /**
     * Initializes the header behavior.
     *
     * @internal
     */
    protected onMount(_config: AppConfig): void {

        this.header = queryOptional<HTMLElement>(document, HEADER_SELECTOR);

        if (!this.header) {
            return;
        }

        this.header.classList.remove(HIDDEN_CLASS);

        this.lastScrollY = window.scrollY;

        window.addEventListener('scroll', this.handleScroll, { passive: true });
        this.updateHeaderState();
    }

    /**
     * Cleans up all side effects.
     *
     * Removes:
     * - scroll listener
     * - CSS state classes
     *
     * @internal
     */
    protected onDestroy() {
        super.onDestroy();
        window.removeEventListener('scroll', this.handleScroll);

        if (!this.header) {
            return;
        }

        this.header.classList.remove(
            SCROLLED_CLASS,
            HIDDEN_CLASS,
            MENU_OPEN_CLASS,
        );

        this.header = null;
        this.lastScrollY = 0;
    }

    /**
     * Handles scroll events.
     */
    private handleScroll = (): void => {
        this.updateHeaderState();
    }

    /**
     * Updates header state based on scroll position and direction.
     */
    private updateHeaderState(): void {
        if (!this.header) {
            return;
        }

        const currentScrollY = window.scrollY;
        const isScrolled = currentScrollY > 10;
        const isScrollingDown = currentScrollY > this.lastScrollY;
        const isMenuOpen = this.header.classList.contains(MENU_OPEN_CLASS);

        this.header.classList.toggle(SCROLLED_CLASS, isScrolled);

        const shouldHide = isScrollingDown && !isMenuOpen;

        this.header.classList.toggle(HIDDEN_CLASS, shouldHide);

        this.lastScrollY = currentScrollY;
    }
}