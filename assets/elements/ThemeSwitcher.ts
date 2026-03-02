import { getRadius } from "../helpers/Maths";

export type ThemeName = "day-theme" | "night-theme";

export type ThemeSwitcherEventType = "click" | "pointerup";

export interface ThemeSwitcherOptions {
    defaultTheme?: ThemeName;
    getInitialTheme?: () => ThemeName | null | undefined;

    onBeforeChange?: (next: ThemeName, current: ThemeName) => Promise<void> | void;
    onChange?: (next: ThemeName) => Promise<void> | void;
    onAfterChange?: (next: ThemeName, current: ThemeName) => Promise<void> | void;

    toggleSelector?: string;

    /**
     * Handle event only if toggle element is inside this selector context.
     * Example: ".header" (only allow header toggle)
     */
    enabledSelector?: string;

    /**
     * Ignore toggle element if it is inside this selector context.
     * Example: ".drawer.is-open, .modal.is-open, [data-overlay='open']"
     */
    disabledSelector?: string;

    /**
     * Last gate before handling. If returns false => ignore.
     * Useful for custom logic (e.g. ignore if drawer open from JS state).
     */
    shouldHandle?: (toggleEl: HTMLElement, event: Event) => boolean;

    /**
     * Event type used for delegation.
     */
    eventType?: ThemeSwitcherEventType;

    /**
     * Event behavior
     */
    preventDefault?: boolean;
    stopPropagation?: boolean;

    duration?: number;
    easing?: string;

    useViewTransition?: boolean;
    storageKey?: string;

    /**
     * Optional: ignore if switching is already in progress.
     * Keep true by default (prevents spam / double anim).
     */
    lockDuringSwitch?: boolean;
}

type ViewTransition = { ready: Promise<void>; finished: Promise<void> };
type DocumentWithViewTransition = Document & {
    startViewTransition?: (callback: () => void) => ViewTransition;
};

const DEFAULTS: Required<ThemeSwitcherOptions> = {
    defaultTheme: "night-theme",
    getInitialTheme: () => null,

    onBeforeChange: async () => {},
    onChange: async () => {},
    onAfterChange: async () => {},

    toggleSelector: ".theme-toggle",

    enabledSelector: "",
    disabledSelector: "",
    shouldHandle: () => true,

    eventType: "click",
    preventDefault: false,
    stopPropagation: false,

    duration: 520,
    easing: "ease-out",

    useViewTransition: true,
    storageKey: "theme",

    lockDuringSwitch: true,
};

export class ThemeSwitcher {
    static readonly THEMES: readonly ThemeName[] = ["day-theme", "night-theme"] as const;

    readonly options: Readonly<Required<ThemeSwitcherOptions>>;

    #bound = false;
    #switching = false;
    #handler: ((e: Event) => void) | null = null;

    constructor(customOptions: ThemeSwitcherOptions = {}) {
        this.options = Object.freeze({
            ...DEFAULTS,
            ...customOptions
        });
    }

    init(): this {
        if (this.#bound) return this;

        this.#bound = true;
        this.applyInitialTheme();

        this.#handler = (e: Event) => void this.handleEvent(e);
        document.addEventListener(this.options.eventType, this.#handler);

        return this;
    }

    destroy(): void {
        if (!this.#bound) return;

        if (this.#handler) {
            document.removeEventListener(this.options.eventType, this.#handler);
        }

        this.#bound = false;
        this.#switching = false;
        this.#handler = null;
    }

    applyInitialTheme(): void {
        const fromApp = this.#sanitizeTheme(this.options.getInitialTheme());
        const fromStorage = this.#sanitizeTheme(this.#safeGetStorage(this.options.storageKey));
        this.applyTheme(fromApp ?? fromStorage ?? this.options.defaultTheme);
    }

    applyTheme(theme: ThemeName): void {
        const body = document.body;
        if (!body) return;

        body.classList.remove(...ThemeSwitcher.THEMES);
        body.classList.add(theme);

        // handy for CSS var themes too
        body.dataset.theme = theme;

        this.#safeSetStorage(this.options.storageKey, theme);
    }

    getCurrentTheme(): ThemeName {
        const body = document.body;
        if (!body) return this.options.defaultTheme;
        return body.classList.contains("night-theme") ? "night-theme" : "day-theme";
    }

    async switchTheme(theme: ThemeName, originEl: HTMLElement): Promise<void> {
        const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        const doc = document as DocumentWithViewTransition;
        const canVT = Boolean(this.options.useViewTransition && doc.startViewTransition);

        if (!canVT || reduceMotion) {
            this.applyTheme(theme);
            return;
        }

        const { x, y, radius } = getRadius(originEl);

        const transition = doc.startViewTransition!(() => {
            this.applyTheme(theme);
        });

        await transition.ready;
        await new Promise<void>((r) => requestAnimationFrame(() => r()));

        try {
            document.documentElement.animate(
                {
                    clipPath: [
                        `circle(0px at ${x}px ${y}px)`,
                        `circle(${radius}px at ${x}px ${y}px)`,
                    ],
                    opacity: [0.55, 1, 1],
                    offset: [0, 0.22, 1],
                },
                {
                    duration: this.options.duration,
                    easing: this.options.easing,
                    pseudoElement: "::view-transition-new(root)",
                } as KeyframeAnimationOptions
            );
        } catch {
            // pseudo-element may not exist at that exact moment (Turbo timing).
            // Theme is already applied; ignore.
        }

        // Optional: wait, helps prevent spam during the VT
        try {
            await transition.finished;
        } catch {
            // ignore
        }
    }

    // ------------------------
    // Internal gating
    // ------------------------

    #getNextTheme(): ThemeName {
        return this.getCurrentTheme() === "night-theme" ? "day-theme" : "night-theme";
    }

    #sanitizeTheme(v: unknown): ThemeName | null {
        return ThemeSwitcher.THEMES.includes(v as ThemeName) ? (v as ThemeName) : null;
    }

    #passesEnabled(toggleEl: HTMLElement): boolean {
        const sel = this.options.enabledSelector;
        if (!sel) return true;
        return Boolean(toggleEl.closest(sel));
    }

    #passesDisabled(toggleEl: HTMLElement): boolean {
        const sel = this.options.disabledSelector;
        if (!sel) return true;
        return !toggleEl.closest(sel);
    }

    async handleEvent(e: Event): Promise<void> {
        const target = e.target;
        if (!(target instanceof Element)) return;

        const toggle = target.closest(this.options.toggleSelector);
        if (!(toggle instanceof HTMLElement)) return;

        // Optional event behavior
        if (this.options.preventDefault && "preventDefault" in e) {
            (e as Event).preventDefault();
        }
        if (this.options.stopPropagation && "stopPropagation" in e) {
            (e as Event).stopPropagation();
        }

        // Gates
        if (!this.#passesEnabled(toggle)) return;
        if (!this.#passesDisabled(toggle)) return;
        if (!this.options.shouldHandle(toggle, e)) return;

        if (this.options.lockDuringSwitch && this.#switching) return;
        this.#switching = true;

        const current = this.getCurrentTheme();
        const next = this.#getNextTheme();

        try {
            await this.options.onBeforeChange(next, current);
            await this.switchTheme(next, toggle);
            await this.options.onChange(next);
            await this.options.onAfterChange(next, current);
        } finally {
            this.#switching = false;
        }
    }

    #safeGetStorage(key: string): string | null {
        try {
            return localStorage.getItem(key);
        } catch {
            return null;
        }
    }

    #safeSetStorage(key: string, value: string): void {
        try {
            localStorage.setItem(key, value);
        } catch {
            // ignore
        }
    }

}