import {
    ThemeManagerContract,
    ThemeManagerOptions,
    ThemeStorage
} from "../Contracts";
import {ThemeName, THEMES} from "../types";

/**
 * Default theme manager implementation.
 *
 * Responsible for:
 * - applying the active theme class on the root element
 * - removing inactive theme classes
 * - toggling between supported themes
 * - persisting manual theme changes
 */
export class ThemeManager implements ThemeManagerContract {
    private readonly root: HTMLElement;
    private currentTheme: ThemeName;

    constructor(
        private readonly storage: ThemeStorage,
        options: ThemeManagerOptions = {},
    ) {
        this.root = options.root ?? document.body;
        this.currentTheme = options.defaultTheme ?? 'day-theme';
    }

    /**
     * Apply the initial theme to the DOM.
     */
    init(): void {
        this.apply(this.currentTheme);
    }

    /**
     * Toggle between day-theme and night-theme.
     */
    toggle(): ThemeName {
        const nextTheme: ThemeName =
            this.currentTheme === 'day-theme' ? 'night-theme' : 'day-theme';

        this.set(nextTheme);

        return nextTheme;
    }


    /**
     * Apply and persist a specific theme.
     */
    set(theme: ThemeName): void {
        this.apply(theme);
        this.storage.set(theme);
    }

    /**
     * Get the active theme.
     */
    getCurrentTheme(): ThemeName {
        return this.currentTheme;
    }

    /**
     * Apply the theme class to the root element.
     */
    private apply(theme: ThemeName): void {
        this.root.classList.remove(...THEMES);
        this.root.classList.add(theme);

        this.currentTheme = theme;
    }

}