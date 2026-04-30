import {ThemeName} from "../types";

/**
 * Public contract for the theme manager.
 */
export interface ThemeManagerContract {
    /**
     * Apply the initial theme.
     */
    init(): void;

    /**
     * Toggle between available themes.
     */
    toggle(): ThemeName;

    /**
     * Apply a specific theme.
     */
    set(theme: ThemeName): void;

    /**
     * Get the active theme.
     */
    getCurrentTheme(): ThemeName;
}