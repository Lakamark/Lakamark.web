import {ThemeStorage} from "../Contracts/ThemeStorage";
import {isTheme, Theme} from "../types/Theme";

/**
 * Stores the selected theme in `localStorage`.
 *
 * Invalid stored values are ignored and returned as `null`.
 */
export class LocalStorageThemeStorage implements ThemeStorage {
    private readonly key: string;

    constructor(key = 'lmk-theme') {
        this.key = key;
    }

    get(): Theme | null {
        const value: string|null = localStorage.getItem(this.key);

        return isTheme(value) ? value : null;
    }

    set(theme: Theme): void {
        localStorage.setItem(this.key, theme);
    }
}