/**
 * Represents a supported theme identifier.
 *
 * The value is the stable internal name used by the application.
 */
export type Theme = 'day' | 'night';

/**
 * Describes how a theme is represented by the application.
 *
 * `className` is the CSS root class applied to <body>.
 */
export type ThemeConfig = {
    name: Theme;
    className: string;
    userSelectable: boolean;
};

/**
 * List of themes supported by the application.
 *
 * Keep this list in sync with SCSS root theme classes:
 * - `day-theme`
 * - `night-theme`
 */
export const THEMES: ThemeConfig[] = [
    { name: 'day', className: 'day-theme', userSelectable: true },
    { name: 'night', className: 'night-theme', userSelectable: true },
];


/**
 * Checks if a value is a supported theme.
 */
export function isTheme(value: unknown): value is Theme {
    return THEMES.some((theme: ThemeConfig): boolean => theme.name === value);
}

/**
 * Returns the CSS class name associated with a theme.
 */
export function getThemeClassName(theme: Theme): string {
    return THEMES.find((config: ThemeConfig): boolean => config.name === theme)?.className ?? 'day-theme';
}

/**
 * Returns all CSS class names used by supported themes.
 */
export function getAllThemeClassNames(): string[] {
    return THEMES.map((theme: ThemeConfig): string => theme.className);
}