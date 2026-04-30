/**
 * Available theme class names.
 *
 * These values must match the CSS classes applied on <body>.
 */
export const THEMES = ['day-theme', 'night-theme'] as const;

/**
 * Supported application theme.
 */
export type ThemeName = (typeof THEMES)[number];
