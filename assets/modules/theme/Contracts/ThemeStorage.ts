import {ThemeName} from "../types";

export interface ThemeStorage {
    /**
     * Retrieve the stored theme.
     */
    get(): ThemeName | null;

    /**
     * Store the active theme.
     */
    set(theme: ThemeName): void;
}