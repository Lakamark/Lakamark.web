import { throttle } from "../helpers/Timer.js";
import {
    clearAllBodyScrollLocks,
    disableBodyScroll,
    enableBodyScroll,
} from "body-scroll-lock";

/**
 * HeaderUI micro-library
 *
 * A small, framework-agnostic controller for a responsive site header.
 *
 * Features
 * - Desktop scroll behavior:
 *   - DEFAULT at the top of the page
 *   - HIDDEN when scrolling down
 *   - FIXED when scrolling up (and after the threshold)
 * - Mobile behavior:
 *   - Never hides the header (no HIDDEN state under a breakpoint)
 * - Mobile hamburger / off-canvas toggle
 * - Optional scroll container support (when the page scrolls inside an element)
 * - Turbo-safe if `destroy()` is called before navigation cache
 *
 * Required markup (default selectors)
 * - Header root:       <header id="main-header" class="header">
 * - Hamburger button:  <button id="js-hamburger" class="header__hamburger">
 * - Nav panel:         <nav class="header__nav"> ... </nav>
 *
 * CSS contract (classes toggled by HeaderUI)
 * - `is-fixed`        Applied to header when in FIXED state
 * - `is-hidden`       Applied to header when in HIDDEN state
 * - `visible--menu`   Applied to header when mobile menu is open
 * - `has-fixed-header` Applied to `.page-wrapper` when FIXED (layout offset)
 *
 * Notes
 * - `has-fixed-header` is toggled on `.page-wrapper` (not on <body>).
 *   Your layout can offset content with:
 *     .page-wrapper.has-fixed-header { padding-top: var(--header-height); }
 *
 * Basic usage
 * @example
 * const header = new HeaderUI().init();
 * // ...
 * header.destroy();
 *
 * Customizing selectors / behavior
 * @example
 * const header = new HeaderUI({
 *   selectors: {
 *     header: "#main-header",
 *     hamburger: "#js-hamburger",
 *     nav: ".header__nav",
 *     scrollContainer: ".page-content", // optional
 *   },
 *   scroll: {
 *     delta: 24,
 *     throttleMs: 80,
 *     disableHideUnder: 980,
 *   },
 *   hooks: {
 *     onStateChange: (next, prev) => console.log({ next, prev }),
 *     onMenuToggle: (open) => console.log("menu open?", open),
 *   },
 * }).init();
 *
 * @typedef {Object} HeaderUIOptions
 *
 * @property {Object} selectors
 * @property {string} selectors.header          CSS selector for the header root.
 * @property {string} selectors.hamburger       CSS selector for the hamburger button.
 * @property {string} selectors.nav             CSS selector for the navigation panel (off-canvas on mobile).
 * @property {string|null} selectors.scrollContainer
 *   Optional CSS selector for a custom scrolling container (e.g. ".page-content").
 *   If null, the library uses the document scrolling element.
 *
 * @property {Object} classes
 * @property {string} classes.menuOpen          Class applied to header when menu is open. (default: "visible--menu")
 * @property {string} classes.hidden            Class applied to header when hidden.    (default: "is-hidden")
 * @property {string} classes.fixed             Class applied to header when fixed.     (default: "is-fixed")
 * @property {string} classes.bodyFixed
 *   Class applied to `.page-wrapper` when header is fixed to offset layout. (default: "has-fixed-header")
 *
 * @property {Object} scroll
 * @property {number} scroll.delta              Minimum scroll delta (px) before changing state.
 * @property {number} scroll.throttleMs         Throttle delay (ms) for scroll handler.
 * @property {number} scroll.resizeThrottleMs   Throttle delay (ms) for resize handler.
 * @property {number} scroll.disableHideUnder
 *   Breakpoint (px). Under this width, the header will never enter HIDDEN state.
 *
 * @property {Object} hooks
 * @property {(function(number, number): void) | null} hooks.onStateChange
 *   Called after a state transition: (newState, oldState).
 * @property {(function(boolean): void) | null} hooks.onMenuToggle
 *   Called when the mobile menu opens/closes: (isOpen).
 */
export class HeaderUI {
    /**
     * Available header states.
     * @readonly
     * @enum {number}
     */
    static STATES = {
        FIXED: 0,
        HIDDEN: 1,
        DEFAULT: 2,
    };

    /**
     * Default configuration.
     * @readonly
     * @type {HeaderUIOptions}
     */
    static DEFAULT_OPTIONS = {
        selectors: {
            header: "#main-header",
            hamburger: "#js-hamburger",
            nav: ".header__nav",
            scrollContainer: null,
        },
        classes: {
            menuOpen: "visible--menu",
            hidden: "is-hidden",
            fixed: "is-fixed",
            bodyFixed: "has-fixed-header",
        },
        scroll: {
            delta: 20,
            throttleMs: 100,
            resizeThrottleMs: 150,
            disableHideUnder: 980,
        },
        hooks: {
            onStateChange: null,
            onMenuToggle: null,
        },
    };

    /**
     * @param {Partial<HeaderUIOptions>} [options]
     */
    constructor(options = {}) {
        this.options = this.#mergeOptions(options);

        /** @type {HTMLElement|null} */
        this.header = null;

        /** @type {HTMLElement|null} */
        this.hamburger = null;

        /** @type {HTMLElement|null} */
        this.nav = null;

        /** @type {HTMLElement|null} */
        this.scrollEl = null;

        /** @type {number} */
        this.state = HeaderUI.STATES.DEFAULT;

        /** @type {number} */
        this.previousTop = 0;

        /** @type {number} */
        this.scrollOffset = 0;

        /** @type {boolean} */
        this.ticking = false;

        /** @type {HTMLElement|null} */
        this.wrapper = null;

        /** @private */
        this._onScroll = null;

        /** @private */
        this._onResize = null;

        /** @private */
        this._onClick = null;
    }

    /**
     * Initialize the HeaderUI instance:
     * - Queries DOM elements
     * - Clears any existing body-scroll-lock state
     * - Measures header height
     * - Binds scroll/resize and hamburger click listeners
     *
     * @returns {HeaderUI}
     */
    init() {
        this.#query();
        if (!this.header) return this;

        clearAllBodyScrollLocks();
        this.refresh();

        this.#bindScroll();
        this.#bindMenu();

        return this;
    }

    /**
     * Destroy listeners and cleanup:
     * - Removes window event listeners
     * - Removes hamburger click handler
     * - Resets toggled classes on header and `.page-wrapper`
     * - Clears body scroll locks
     *
     * Call this before Turbo caches a page to avoid duplicate listeners.
     *
     * @returns {void}
     */
    destroy() {
        window.removeEventListener("scroll", this._onScroll);
        window.removeEventListener("resize", this._onResize);

        if (this.hamburger && this._onClick) {
            this.hamburger.removeEventListener("click", this._onClick);
        }

        if (this.header) {
            this.header.classList.remove(
                this.options.classes.menuOpen,
                this.options.classes.hidden,
                this.options.classes.fixed
            );
        }

        this.wrapper?.classList.remove(this.options.classes.bodyFixed);

        clearAllBodyScrollLocks();
    }

    /**
     * Recalculate header measurements.
     * Currently used to store the header height as the "threshold" (scrollOffset).
     *
     * @returns {void}
     */
    refresh() {
        if (!this.header) return;
        this.scrollOffset = this.header.offsetHeight;
    }

    /**
     * Check if mobile menu is open.
     * @returns {boolean}
     */
    isMenuOpen() {
        return this.header?.classList.contains(this.options.classes.menuOpen) ?? false;
    }

    /**
     * Open mobile menu (adds class + locks body scroll on nav panel).
     * @returns {void}
     */
    openMenu() {
        if (!this.header) return;

        this.header.classList.add(this.options.classes.menuOpen);

        if (this.nav) {
            disableBodyScroll(this.nav);
            this.options.hooks.onMenuToggle?.(true);
        }
    }

    /**
     * Close mobile menu (removes class + unlocks body scroll on nav panel).
     * @returns {void}
     */
    closeMenu() {
        if (!this.header) return;

        this.header.classList.remove(this.options.classes.menuOpen);

        if (this.nav) {
            enableBodyScroll(this.nav);
        }

        this.options.hooks.onMenuToggle?.(false);
    }

    /**
     * Toggle mobile menu open/close.
     * @returns {void}
     */
    toggleMenu() {
        this.isMenuOpen() ? this.closeMenu() : this.openMenu();
    }

    /* ============================
       Private Methods
    ============================ */

    /**
     * Deep-merge default options with custom options.
     * @private
     * @param {Partial<HeaderUIOptions>} options
     * @returns {HeaderUIOptions}
     */
    #mergeOptions(options) {
        const d = HeaderUI.DEFAULT_OPTIONS;

        return {
            ...d,
            ...options,
            selectors: { ...d.selectors, ...(options.selectors ?? {}) },
            classes: { ...d.classes, ...(options.classes ?? {}) },
            scroll: { ...d.scroll, ...(options.scroll ?? {}) },
            hooks: { ...d.hooks, ...(options.hooks ?? {}) },
        };
    }

    /**
     * Query DOM elements based on selectors.
     * @private
     * @returns {void}
     */
    #query() {
        this.header = document.querySelector(this.options.selectors.header);
        this.hamburger = document.querySelector(this.options.selectors.hamburger);
        this.nav = document.querySelector(this.options.selectors.nav);
        this.wrapper = document.querySelector(".page-wrapper");

        this.scrollEl = this.options.selectors.scrollContainer
            ? document.querySelector(this.options.selectors.scrollContainer)
            : null;
    }

    /**
     * Bind scroll and resize listeners (throttled + RAF).
     * @private
     * @returns {void}
     */
    #bindScroll() {
        this._onScroll = throttle(() => {
            if (this.ticking) return;
            this.ticking = true;

            requestAnimationFrame(() => {
                this.#update();
                this.ticking = false;
            });
        }, this.options.scroll.throttleMs);

        this._onResize = throttle(() => this.refresh(), this.options.scroll.resizeThrottleMs);

        window.addEventListener("scroll", this._onScroll, { passive: true });
        window.addEventListener("resize", this._onResize);
    }

    /**
     * Bind hamburger click listener (throttled).
     * @private
     * @returns {void}
     */
    #bindMenu() {
        if (!this.hamburger) return;
        this._onClick = throttle(() => this.toggleMenu(), 300);
        this.hamburger.addEventListener("click", this._onClick);
    }

    /**
     * Read scroll position, compute next state, and apply it.
     * @private
     * @returns {void}
     */
    #update() {
        if (!this.header) return;

        const scroller = this.scrollEl || document.scrollingElement || document.documentElement;

        /** @type {number} */
        const currentTop =
            scroller && typeof scroller.scrollTop === "number" ? scroller.scrollTop : window.scrollY || 0;

        const nextState = this.#computeNextState(currentTop);
        this.#setState(nextState);

        this.previousTop = currentTop;
    }

    /**
     * Compute the next header state.
     *
     * Mobile behavior:
     * - Never enters HIDDEN state (under disableHideUnder breakpoint).
     *
     * Desktop behavior:
     * - DEFAULT when near the top
     * - HIDDEN when scrolling down quickly
     * - FIXED when scrolling up or when settled beyond the threshold
     *
     * @private
     * @param {number} currentTop
     * @returns {number}
     */
    #computeNextState(currentTop) {
        const { FIXED, HIDDEN, DEFAULT } = HeaderUI.STATES;

        if (this.isMenuOpen()) return FIXED;

        const w = window.innerWidth || document.documentElement.clientWidth;

        // Mobile: never hide header
        if (w < this.options.scroll.disableHideUnder) {
            return currentTop > 0 ? FIXED : DEFAULT;
        }

        // On DESKTOP
        if (currentTop <= this.scrollOffset) {
            return DEFAULT;
        }

        const deltaDown = currentTop - this.previousTop;
        const deltaUp = this.previousTop - currentTop;

        if (deltaDown > this.options.scroll.delta) return HIDDEN;
        if (deltaUp > this.options.scroll.delta) return FIXED;

        // Ensure we don't remain hidden due to micro-scroll/throttle
        return FIXED;
    }

    /**
     * Apply a new header state by toggling CSS classes and layout offset class.
     *
     * - HIDDEN  => add `is-hidden`, remove `is-fixed`, remove wrapper offset
     * - FIXED   => add `is-fixed`, remove `is-hidden`, add wrapper offset
     * - DEFAULT => remove both, remove wrapper offset
     *
     * @private
     * @param {number} newState
     * @returns {void}
     */
    #setState(newState) {
        if (newState === this.state) return;

        const { hidden, fixed } = this.options.classes;
        const { FIXED, HIDDEN, DEFAULT } = HeaderUI.STATES;

        const oldState = this.state;

        if (!this.header) return;

        if (newState === HIDDEN) {
            this.header.classList.add(hidden);
            this.header.classList.remove(fixed);
            this.#syncBodyFixed(false);
        } else if (newState === FIXED) {
            this.header.classList.remove(hidden);
            this.header.classList.add(fixed);
            this.#syncBodyFixed(true);
        } else if (newState === DEFAULT) {
            this.header.classList.remove(hidden);
            this.header.classList.remove(fixed);
            this.#syncBodyFixed(false);
        }

        this.state = newState;
        this.options.hooks.onStateChange?.(newState, oldState);
    }

    /**
     * Toggle the layout offset class on `.page-wrapper`.
     * @private
     * @param {boolean} isFixed
     * @returns {void}
     */
    #syncBodyFixed(isFixed) {
        this.wrapper.classList.toggle(this.options.classes.bodyFixed, isFixed);
    }
}
