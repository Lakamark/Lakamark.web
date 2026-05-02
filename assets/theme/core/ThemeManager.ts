import {Theme} from "../types/Theme";
import {
    ThemeApplier,
    ThemeResolver,
    ThemeStorage
} from "../Contracts";
import {AppContext} from "../../core";

/**
 * Orchestrates the theme system.
 *
 * Responsibilities:
 * - Resolve the initial theme (via ThemeResolver)
 * - Apply the theme (via ThemeApplier)
 * - Persist the theme (via ThemeStorage)
 * - Expose a simple API to control the theme
 *
 * This class contains NO DOM logic and NO storage logic directly.
 * All side effects are delegated to injected dependencies.
 *
 * Lifecycle:
 * - `init()` must be called once before usage
 * - `destroy()` cleans up internal state (future-proof)
 */
export class ThemeManager {
    private currentTheme: Theme | null = null;
    private initialized: boolean = false;

    constructor(
        private readonly applier: ThemeApplier,
        private readonly storage: ThemeStorage,
        private readonly resolver: ThemeResolver,
    ) {}

    /**
     * Initializes the theme system.
     *
     * Order of resolution:
     * 1. Stored theme (if available)
     * 2. Resolved theme from context
     *
     * @param context - Application context
     */
    init(context: AppContext): void {
        if (this.initialized) {
            return;
        }

        const storedTheme = this.storage.get();
        const resolvedTheme = this.resolver.resolve(context);

        const theme = storedTheme ?? resolvedTheme;

        this.apply(theme);

        this.initialized = true;
    }

    /**
     * Sets and applies a theme.
     *
     * @param theme - Theme to apply
     */
    setTheme(theme: Theme): void {
        this.apply(theme);
        this.storage.set(theme);
    }

    /**
     * Toggles between available themes.
     *
     * Current implementation assumes:
     * - 'day' ↔ 'night'
     */
    toggle(): void {
        const next: Theme = this.currentTheme === 'day' ? 'night' : 'day';
        this.setTheme(next);
    }

    /**
     * Returns the currently active theme.
     */
    getTheme(): Theme | null {
        return this.currentTheme;
    }

    /**
     * Cleans up internal state.
     *
     * Reserved for future extensions (listeners, subscriptions, etc.)
     */
    destroy(): void {
        this.currentTheme = null;
        this.initialized = false;
    }

    /**
     * Applies a theme internally.
     *
     * @param theme - Theme to apply
     */
    private apply(theme: Theme): void {
        this.applier.apply(theme);
        this.currentTheme = theme;
    }
}