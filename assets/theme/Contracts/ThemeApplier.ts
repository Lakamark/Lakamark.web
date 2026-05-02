import {Theme} from "../types/Theme";

/**
 * Responsible for applying a theme to the UI.
 *
 * This contract abstracts how a theme is reflected in the DOM.
 * Example implementations:
 * - Apply CSS class on <body>
 * - Apply data attributes
 * - Inject CSS variables dynamically
 *
 * This layer MUST NOT contain business logic.
 * It only reflects the current theme visually.
 */
export interface ThemeApplier {
    /**
     * Applies the given theme to the UI.
     *
     * @param theme - The theme to apply
     */
    apply(theme: Theme): void;
}