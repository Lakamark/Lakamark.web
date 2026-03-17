import {getRadius} from "../helper/Maths";

export type ThemeName = "day-theme" | "night-theme";

export interface ThemeManagerOptions {
    defaultTheme?: ThemeName;
    storageKey?: string;
    useViewTransition?: boolean;
    duration?: number;
    easing?: string;
    lockDuringSwitch?: boolean;

    getInitialTheme?: () => ThemeName | null | undefined;

    onBeforeChange?: (next: ThemeName, current: ThemeName) => Promise<void> | void;
    onChange?: (next: ThemeName) => Promise<void> | void;
    onAfterChange?: (next: ThemeName, current: ThemeName) => Promise<void> | void;
}

type ViewTransition = {
    ready: Promise<void>;
    finished: Promise<void>;
};

type DocumentWithViewTransition = Document & {
    startViewTransition?: (callback: () => void) => ViewTransition;
};

const DEFAULTS: Required<ThemeManagerOptions> = {
    defaultTheme: "night-theme",
    storageKey: "theme",
    useViewTransition: true,
    duration: 520,
    easing: "ease-out",
    lockDuringSwitch: true,

    getInitialTheme: () => null,

    onBeforeChange: async () => {},
    onChange: async () => {},
    onAfterChange: async () => {},
};

export class ThemeManager {
    static readonly THEMES: readonly ThemeName[] = ["day-theme", "night-theme"] as const;

    readonly options: Readonly<Required<ThemeManagerOptions>>;

    #initialized = false;
    #switching = false;

    constructor(options: ThemeManagerOptions = {}) {
        this.options = Object.freeze({
            ...DEFAULTS,
            ...options,
        });
    }

    boot(): this {
        if (this.#initialized) {
            return this;
        }

        this.#initialized = true;
        this.applyInitialTheme();

        return this;
    }

    destroy(): void {
        this.#initialized = false;
        this.#switching = false;
    }

    applyInitialTheme(): void {
        const theme =
            this.#resolveInitialTheme() ??
            this.#resolveStoredTheme() ??
            this.options.defaultTheme;

        this.applyTheme(theme);
    }

    applyTheme(theme: ThemeName): void {
        const body = document.body;
        if (!(body instanceof HTMLBodyElement)) {
            return;
        }

        body.classList.remove(...ThemeManager.THEMES);
        body.classList.add(theme);
        body.dataset.theme = theme;

        this.#writeStorage(theme);
    }

    getCurrentTheme(): ThemeName {
        const body = document.body;

        if (!(body instanceof HTMLBodyElement)) {
            return this.options.defaultTheme;
        }

        if (body.classList.contains("day-theme")) {
            return "day-theme";
        }

        if (body.classList.contains("night-theme")) {
            return "night-theme";
        }

        return this.options.defaultTheme;
    }

    getNextTheme(): ThemeName {
        return this.getCurrentTheme() === "night-theme"
            ? "day-theme"
            : "night-theme";
    }

    async toggle(originEl?: HTMLElement | null): Promise<ThemeName> {
        return this.switchTo(this.getNextTheme(), originEl);
    }

    async switchTo(next: ThemeName, originEl?: HTMLElement | null): Promise<ThemeName> {
        const current = this.getCurrentTheme();

        if (current === next) {
            return current;
        }

        if (this.options.lockDuringSwitch && this.#switching) {
            return current;
        }

        this.#switching = true;

        try {
            await this.options.onBeforeChange(next, current);
            await this.#applyWithTransition(next, originEl);
            await this.options.onChange(next);
            await this.options.onAfterChange(next, current);

            return next;
        } finally {
            this.#switching = false;
        }
    }

    #resolveInitialTheme(): ThemeName | null {
        return this.#sanitizeTheme(this.options.getInitialTheme());
    }

    #resolveStoredTheme(): ThemeName | null {
        return this.#sanitizeTheme(this.#readStorage());
    }

    #sanitizeTheme(value: unknown): ThemeName | null {
        return ThemeManager.THEMES.includes(value as ThemeName)
            ? (value as ThemeName)
            : null;
    }

    async #applyWithTransition(theme: ThemeName, originEl?: HTMLElement | null): Promise<void> {
        const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        const doc = document as DocumentWithViewTransition;
        const canUseTransition =
            this.options.useViewTransition &&
            originEl instanceof HTMLElement &&
            doc.startViewTransition;

        if (!canUseTransition || reduceMotion) {
            this.applyTheme(theme);
            return;
        }

        const { x, y, radius } = getRadius(originEl);

        const transition = doc.startViewTransition!(() => {
            this.applyTheme(theme);
        });

        await transition.ready;
        await new Promise<void>((resolve) => requestAnimationFrame(() => resolve()));

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
            // Ignore animation timing mismatch
        }

        try {
            await transition.finished;
        } catch {
            // Ignore transition cancellation
        }
    }

    #readStorage(): string | null {
        try {
            return localStorage.getItem(this.options.storageKey);
        } catch {
            return null;
        }
    }

    #writeStorage(theme: ThemeName): void {
        try {
            localStorage.setItem(this.options.storageKey, theme);
        } catch {
            // Ignore storage errors
        }
    }
}