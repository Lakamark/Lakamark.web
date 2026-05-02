import {AppContext} from "../../core";
import {createFakeConfig} from "./createFakeAppConfig";

export function createFakeContext(
    overrides: Partial<AppContext> = {}
): AppContext {
    const baseContext = {
        config: createFakeConfig(),
        document,
        window
    } satisfies AppContext;

    return {
        ...baseContext,
        ...overrides
    }
}