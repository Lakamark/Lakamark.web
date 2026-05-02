import type {AppConfig} from "../../dom/contracts";

export function createFakeConfig(
    overrides: Partial<AppConfig> = {}
): AppConfig {
    const baseConfig = {
        userId: null,
        roles: [],
        isPremium: false,
        isLogged: false,
        preferredTheme: null,
        language: 'en',
        environment: 'prod',
    } satisfies AppConfig;

    return {
        ...baseConfig,
        ...overrides,
    };
}