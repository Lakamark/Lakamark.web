import {AppContext} from "../contracts";
import {loadConfig} from "../../dom";

/**
 * Creates a fresh AppContext.
 *
 * Must be called on each Turbo navigation to ensure:
 * - fresh DOM reference
 * - up-to-date config (from Twig)
 */
export function buildAppContext(): AppContext {
    return {
        config: loadConfig(),
        document,
        window,
    };
}