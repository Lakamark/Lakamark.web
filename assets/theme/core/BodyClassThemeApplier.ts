import {ThemeApplier} from "../Contracts";
import {
    getAllThemeClassNames,
    getThemeClassName,
    Theme
} from "../types/Theme";

/**
 * Applies the theme by updating CSS classes on <body>.
 *
 * Behavior:
 * - Removes all known theme classes
 * - Adds the class corresponding to the current theme
 *
 * Example:
 * - `day`   → adds `day-theme`
 * - `night` → adds `night-theme`
 *
 * This is the default implementation used by the application.
 */
export class BodyClassThemeApplier  implements ThemeApplier {
    apply(theme: Theme): void {
        const body: HTMLElement = document.body;

        // Remove all theme classes
        body.classList.remove(...getAllThemeClassNames());

        // Add the current theme class
        body.classList.add(getThemeClassName(theme));
    }
}