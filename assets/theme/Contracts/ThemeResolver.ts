import {AppContext} from "../../core";
import {Theme} from "../types/Theme";

/**
 * Resolves which theme should be used when the application starts.
 *
 * This contract hides the strategy used to determine the initial theme.
 *
 * Examples:
 * - stored user preference
 * - Symfony/Twig config
 * - browser system preference
 * - default fallback theme
 */
export interface ThemeResolver {
    /**
     * Resolves the initial theme from the application context.
     *
     * @param context - Current application context.
     */
    resolve(context: AppContext): Theme;
}