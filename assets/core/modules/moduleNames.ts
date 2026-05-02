/**
 * Central registry of application module names.
 *
 * These names are used as stable identifiers for modules
 * registered inside the application lifecycle.
 */
export const MODULE_NAMES = {
    THEME: 'theme',
    DEBUG: 'debug',
    HEADER: 'header',
    MENU: 'menu',
} as const;

/**
 * Represents a valid application module name.
 *
 * Derived from MODULE_NAMES to keep the type automatically
 * synchronized with the registry.
 */
export type ModuleName = typeof MODULE_NAMES[keyof typeof MODULE_NAMES];