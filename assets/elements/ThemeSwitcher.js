import { getRadius } from "../helpers/Maths.js"

/**
 * Theme switcher (body class) with ViewTransition animation.
 * Turbo-friendly: single delegated listener + destroy().
 *
 * @typedef {"day-theme" | "night-theme"} ThemeName
 * @typedef ThemeSwitcherOptions
 * @property {ThemeName} [defaultTheme]
 * @property {() => ThemeName | null | undefined} [getInitialTheme]
 * @property {(theme: ThemeName) => Promise<void> | void} [onChange]
 * @property {string} [toggleSelector]
 * @property {number} [duration]
 * @property {string} [easing]
 */

export class ThemeSwitcher {
    /** @type {string} */
    static STORAGE_KEY = "theme";

    /** @type {ThemeName[]} */
    static THEMES = ["day-theme", "night-theme"];

    /** @type {Required<ThemeSwitcherOptions>} */
    options;

    /** @type {boolean} */
    #bound = false;

    /** @type {boolean} */
    #switching = false;

    /** @type {(e: MouseEvent) => void | Promise<void> | null} */
    #onClick = null;

    /**
     * @param {ThemeSwitcherOptions} [customOptions]
     */
    constructor(customOptions = {}) {
        /** @type {Required<ThemeSwitcherOptions>} */
        const defaults = {
            defaultTheme: "night-theme",
            getInitialTheme: () => null,
            onChange: async () => {},
            toggleSelector: ".theme-toggle",
            duration: 520,
            easing: "ease-out",
        };

        this.options = Object.freeze({
            ...defaults,
            ...customOptions,
        });
    }

    /**
     * Init: apply initial theme + attach delegated click listener.
     * @returns {ThemeSwitcher}
     */
    init() {
        if (this.#bound) return this;

        this.#bound = true;
        this.applyInitialTheme();

        this.#onClick = (e) => this.#handleClick(e);
        document.addEventListener("click", this.#onClick);

        return this;
    }

    /**
     * Destroy: remove listener (Turbo before-cache safe).
     */
    destroy() {
        if (!this.#bound) return;

        if (this.#onClick) {
            document.removeEventListener("click", this.#onClick);
        }

        this.#bound = false;
        this.#switching = false;
        this.#onClick = null;
    }

    /**
     * Apply theme from:
     * 1) backend override (getInitialTheme)
     * 2) localStorage
     * 3) defaultTheme
     */
    applyInitialTheme() {
        const fromApp = this.#sanitizeTheme(this.options.getInitialTheme());
        const fromStorage = this.#sanitizeTheme(
            localStorage.getItem(ThemeSwitcher.STORAGE_KEY)
        );
        const fallback = this.#sanitizeTheme(this.options.defaultTheme) ?? "night-theme";

        this.applyTheme(fromApp ?? fromStorage ?? fallback);
    }

    /**
     * Apply a theme to <body> and persist to localStorage.
     * @param {ThemeName} theme
     */
    applyTheme(theme) {
        document.body.classList.remove(...ThemeSwitcher.THEMES);
        document.body.classList.add(theme);
        localStorage.setItem(ThemeSwitcher.STORAGE_KEY, theme);
    }

    /**
     * @param {unknown} theme
     * @returns {ThemeName | null}
     */
    #sanitizeTheme(theme) {
        return ThemeSwitcher.THEMES.includes(/** @type {ThemeName} */ (theme))
            ? /** @type {ThemeName} */ (theme)
            : null;
    }

    /**
     * @returns {ThemeName}
     */
    #getNextTheme() {
        return document.body.classList.contains("night-theme")
            ? "day-theme"
            : "night-theme";
    }

    /**
     * Delegated click handler
     * @param {MouseEvent} e
     */
    async #handleClick(e) {
        const btn =
            e.target instanceof Element
                ? e.target.closest(this.options.toggleSelector)
                : null;

        if (!btn) return;
        if (this.#switching) return;

        this.#switching = true;

        const next = this.#getNextTheme();

        try {
            await this.switchTheme(next, /** @type {HTMLElement} */ (btn));
            await this.options.onChange(next);
        } finally {
            this.#switching = false;
        }
    }

    /**
     * Switch with ViewTransition when available (else fallback).
     * @param {ThemeName} theme
     * @param {HTMLElement} element
     */
    async switchTheme(theme, element) {
        const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (!document.startViewTransition || reduceMotion) {
            this.applyTheme(theme);
            return;
        }

        const { x, y, radius } = getRadius(element);

        const transition = document.startViewTransition(() => {
            this.applyTheme(theme);
        });

        await transition.ready;

        // Let pseudo-elements exist/paint (helps avoid "No elements found")
        await new Promise(requestAnimationFrame);

        try {
            document.documentElement.animate(
                {
                    clipPath: [
                        `circle(0px at ${x}px ${y}px)`,
                        `circle(${radius}px at ${x}px ${y}px)`,
                    ],
                    // cheap "flash" (no blur)
                    opacity: [0.55, 1, 1],
                    offset: [0, 0.22, 1],
                },
                {
                    duration: this.options.duration,
                    easing: this.options.easing,
                    pseudoElement: "::view-transition-new(root)",
                }
            );
        } catch {
            // If Turbo swaps at a bad time, pseudo-element may not exist.
            // Theme is already applied, so we silently ignore.
        }
    }
}