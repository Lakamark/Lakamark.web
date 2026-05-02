import { describe, expect, it } from 'vitest';
import {AppContext} from "../../../core";
import {
    PreferredThemeResolver,
    Theme
} from "../../../theme";
import {
    createFakeConfig,
    createFakeContext
} from "../../factories";

describe('Preferred Theme Resolver', (): void => {
    it('returns preferred theme from context config', (): void => {
        const resolver = new PreferredThemeResolver();

        const context: AppContext = createFakeContext({
            config: createFakeConfig({
                preferredTheme: 'night'
            }),
        });

        expect(resolver.resolve(context)).toBe('night');
    });

    it('falls back to day when preferred theme is null', (): void => {
        const resolver = new PreferredThemeResolver();

        const context: AppContext = createFakeContext({
            config: createFakeConfig({
                preferredTheme: null
            }),
        });

        expect(resolver.resolve(context)).toBe('day');
    });

    it('falls back to day when preferred theme is invalid', (): void => {
        const resolver = new PreferredThemeResolver();

        const context: AppContext = createFakeContext({
            config: createFakeConfig({
                preferredTheme: 'Hyperion corporation' as unknown as Theme
            }),
        });

        expect(resolver.resolve(context)).toBe('day');
    });
});