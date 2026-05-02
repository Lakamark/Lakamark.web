import {AppContext, MountableApp} from "../../core";
import {Mock, vi} from "vitest";

type MockAppRunner = MountableApp & {
    mount: Mock<(context: AppContext) => void>;
    destroy: Mock<() => void>;
};

/**
 * Creates a mock implementation of a MountableApp for tests.
 *
 * The returned app respects the MountableApp contract while exposing
 * Vitest mock functions for lifecycle assertions.
 *
 * Useful for:
 * - Kernel tests
 * - Verifying mount/destroy behavior
 *
 * @returns A mock app with stable mount/destroy methods.
 */
export function createMockAppRunner(): MockAppRunner {
    return {
        mount: vi.fn<(context: AppContext) => void>(),
        destroy: vi.fn<() => void>(),
    };
}
