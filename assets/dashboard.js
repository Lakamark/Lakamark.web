import './css/dashboard.scss';
import './elements/dashboard/index.js'
import {HeaderUI} from "./lib/HeaderUI.js";

let headerInstance = null;

/**
 * Re-init header after every Turbo visit
 */
function initHeader() {
    headerInstance?.destroy();
    headerInstance = new HeaderUI().init();
}

initHeader();
