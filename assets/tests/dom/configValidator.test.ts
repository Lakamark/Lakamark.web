import {describe, it, expect} from "vitest";
import {AppConfig} from "../../dom/contracts";
import {assertAppConfigShape} from "../../dom";

function createValidConfig(): AppConfig {
    return {
        userId: null,
        roles: [],
        isPremium: false,
        isLogged: false,
        environment: 'dev',
        preferredTheme: null,
        language: 'en',
    };
}

describe("configValidator", (): void => {
    it('does not throw for a valid config', (): void => {
        const config = createValidConfig();

        expect(() => assertAppConfigShape(config)).not.toThrow();
    });

    it('throws when config is not an object', (): void => {
        expect(() => assertAppConfigShape(null)).toThrow(
            '[AppConfig] Invalid config: not an object',
        );

        expect(() => assertAppConfigShape('invalid')).toThrow(
            '[AppConfig] Invalid config: not an object',
        );
    });

    it('throws when roles is not an array', (): void => {
        const config = {
            ...createValidConfig(),
            roles: 'admin',
        };

        expect(() => assertAppConfigShape(config)).toThrow(
            '[AppConfig] Invalid roles',
        );
    });

    it('throws when isLogged is not a boolean', (): void => {
        const config = {
            ...createValidConfig(),
            isLogged: 'yes',
        };

        expect(() => assertAppConfigShape(config)).toThrow(
            '[AppConfig] Invalid isLogged',
        );
    });

    it('throws when language is invalid', (): void => {
        const config = {
            ...createValidConfig(),
            language: 'es',
        };

        expect(() => assertAppConfigShape(config)).toThrow(
            '[AppConfig] Invalid language',
        );
    });
})
