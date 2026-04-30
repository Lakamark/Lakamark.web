import {ThemeStorage} from "../Contracts";
import {ThemeName} from "../types";
import {isThemeName} from "./themeGuards";

const STORAGE_KEY = 'lmk-theme';

/**
 * Theme persistence layer based on localStorage.
 */
export class LocalThemeStorage implements ThemeStorage {
    /**
     * Retrieve the stored theme.
     */
    get(): ThemeName | null {
        const value = localStorage.getItem(STORAGE_KEY);

        return isThemeName(value) ? value : null;
    }

    /**
     * Store the active theme.
     */
    set(theme: ThemeName): void {
        localStorage.setItem(STORAGE_KEY, theme);
    }
}