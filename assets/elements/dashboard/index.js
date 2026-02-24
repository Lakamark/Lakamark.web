/**
 * Registers web components intended for the dashboard only.
 *
 * These components are not exposed to the public application.
 *
 * Import this file in the dashboard entry point.
 *
 * @example
 * import './dashboard/elements/index.js';
 */
import Spotlight from "../SpotlightElement/Spotlight.js";

customElements.define('spotlight-bar', Spotlight)