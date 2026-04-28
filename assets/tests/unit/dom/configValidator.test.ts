import { describe, expect, it, vi } from 'vitest';
import {createFakeConfig} from "../../Fake";
import {AppConfig} from "../../../dom";
import {assertAppConfigShape} from "../../../dom/configValidator";

const validRawConfig: AppConfig = createFakeConfig();

describe('assertAppConfigShape', () => {
    it('does not throw for a valid config shape', () => {
        expect(() => assertAppConfigShape(validRawConfig)).not.toThrow();
    });

    it('throws when config is not an object', () => {
        expect(() => assertAppConfigShape(null)).toThrow(
            '[AppConfig] Invalid config: not an object',
        );

        expect(() => assertAppConfigShape('invalid')).toThrow(
            '[AppConfig] Invalid config: not an object',
        );
    });

    it('throws when a required key is missing', () => {
        const rawConfig = {
            ...validRawConfig,
            environment: undefined,
        };

        delete rawConfig.environment;

        expect(() => assertAppConfigShape(rawConfig)).toThrow(
            '[AppConfig] Missing key: environment',
        );
    });

    it('warns when unknown keys are present', () => {
        const warnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});

        assertAppConfigShape({
            ...validRawConfig,
            environement: 'dev',
        });

        expect(warnSpy).toHaveBeenCalledWith(
            '[AppConfig] Unknown keys:',
            ['environement'],
        );

        warnSpy.mockRestore();
    });

    it('throws when environment is misspelled', () => {
        const rawConfig = {
            ...validRawConfig,
            environement: 'dev',
        };

        delete (rawConfig as Partial<typeof validRawConfig>).environment;

        expect(() => assertAppConfigShape(rawConfig)).toThrow(
            '[AppConfig] Missing key: environment',
        );
    })
});