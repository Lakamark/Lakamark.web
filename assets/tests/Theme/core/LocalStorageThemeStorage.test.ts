import { afterEach, describe, expect, it } from 'vitest';
import { LocalStorageThemeStorage } from '../../../theme';

describe('LocalStorageThemeStorage', (): void => {
    afterEach((): void => {
        localStorage.clear();
    });

    it('returns null when no theme is stored', (): void => {
        const storage = new LocalStorageThemeStorage();

        expect(storage.get()).toBeNull();
    });

    it('stores and retrieves a theme', (): void => {
        const storage = new LocalStorageThemeStorage();

        storage.set('night');

        expect(storage.get()).toBe('night');
    });

    it('ignores invalid stored values', (): void => {
        localStorage.setItem('lmk-theme', 'invalid');

        const storage = new LocalStorageThemeStorage();

        expect(storage.get()).toBeNull();
    });
});