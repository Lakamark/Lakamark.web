import {AppConfig} from "./contracts";

/**
 * Ensures the raw DOM config contains the expected AppConfig keys.
 *
 * This validates the object shape before normalization.
 * Value fallback and coercion are handled by readAppConfig().
 */
export function assertAppConfigShape(
    obj: unknown
): asserts obj is Partial<AppConfig> {
    if (typeof obj !== 'object' || obj === null) {
        throw new Error('[AppConfig] Invalid config: not an object');
    }

    const requiredKeys: (keyof AppConfig)[] = [
        'userId',
        'roles',
        'isPremium',
        'isLogged',
        'preferredTheme',
        'language',
        'environment',
    ];

    const record = obj as Record<string, unknown>;

    for (const key of requiredKeys) {
        if (!(key in record)) {
            throw new Error(`[AppConfig] Missing key: ${key}`);
        }
    }

    const unknownKeys: string[] = Object.keys(record).filter(
        (key: string): boolean => !requiredKeys.includes(key as keyof AppConfig),
    );

    if (unknownKeys.length > 0) {
        console.warn('[AppConfig] Unknown keys:', unknownKeys);
    }
}