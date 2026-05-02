import {Theme} from "../types/Theme";

/**
 * Stores and retrieves the selected theme.
 *
 * This contract hides the persistence strategy.
 *
 * Examples:
 * - localStorage
 * - sessionStorage
 * - user profile API
 * - in-memory storage for tests
 */
export interface ThemeStorage {
    /**
     * Returns the stored theme.
     *
     * Returns `null` when no theme has been saved yet.
     */
    get(): Theme | null;

    /**
     * Stores the selected theme.
     *
     * @param theme - Theme to persist.
     */
    set(theme: Theme): void;
}