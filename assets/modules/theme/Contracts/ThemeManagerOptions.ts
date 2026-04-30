import {ThemeName} from "../types";

export interface ThemeManagerOptions {
    /**
     * Root element where theme classes are applied.
     *
     * Defaults to document.body.
     */
    root?: HTMLElement;

    /**
     * Fallback theme.
     */
    defaultTheme?: ThemeName;
}