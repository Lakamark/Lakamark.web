import {AppConfig} from "./contracts";

/**
 * Validates that an unknown value matches the AppConfig shape.
 *
 * Throws an error if validation fails.
 */
export function assertAppConfigShape(
    config: unknown
): asserts config is AppConfig {
    if (typeof config !== 'object' || config === null) {
        throw new Error('[AppConfig] Invalid config: not an object');
    }

    const c = config as AppConfig;

    if (!Array.isArray(c.roles)) {
        throw new Error('[AppConfig] Invalid roles');
    }

    if (typeof c.isLogged !== 'boolean') {
        throw new Error('[AppConfig] Invalid isLogged');
    }

    if (c.language !== 'en' && c.language !== 'fr') {
        throw new Error('[AppConfig] Invalid language');
    }
}