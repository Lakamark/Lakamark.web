import { describe, expect, it, vi } from 'vitest';
import { ThemeManager } from '../../../theme';
import {AppContext} from "../../../core";
import {
    createFakeConfig,
    createFakeContext
} from "../../factories";

describe('ThemeManager', (): void => {
    it('applies stored theme on init if available', (): void => {
        const applier = { apply: vi.fn() };
        const storage = { get: vi.fn().mockReturnValue('night'), set: vi.fn() };
        const resolver = { resolve: vi.fn().mockReturnValue('day') };
        const context: AppContext = createFakeContext();

        const manager = new ThemeManager(applier, storage, resolver);

        manager.init(context);

        expect(applier.apply).toHaveBeenCalledWith('night');
    });

    it('falls back to resolver when no stored theme', (): void => {
        const applier = { apply: vi.fn() };
        const storage = { get: vi.fn().mockReturnValue(null), set: vi.fn() };
        const resolver = { resolve: vi.fn().mockReturnValue('night') };
        const context: AppContext = createFakeContext();

        const manager = new ThemeManager(applier, storage, resolver);

        manager.init(context);

        expect(applier.apply).toHaveBeenCalledWith('night');
    });

    it('toggles theme', (): void => {
        const applier = { apply: vi.fn() };
        const storage = { get: vi.fn().mockReturnValue('day'), set: vi.fn() };
        const resolver = { resolve: vi.fn().mockReturnValue('day') };
        const context: AppContext = createFakeContext({
            config: createFakeConfig({
                preferredTheme: 'day'
            })
        });

        const manager = new ThemeManager(applier, storage, resolver);

        manager.init(context);
        manager.toggle();

        expect(storage.set).toHaveBeenCalledWith('night');
        expect(applier.apply).toHaveBeenLastCalledWith('night');
    });
});