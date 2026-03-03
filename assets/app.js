import './css/app.scss';
import './elements/index.js'

import * as Turbo from "@hotwired/turbo"
import {HeaderUI} from "./lib/HeaderUI.js";
import {ThemeSwitcher} from "./elements/ThemeSwitcher.ts";
import {getLmkConfigSafe, resetLmkConfigCache} from "./helpers/config";


let headerInstance = null;
let theme = null;

/**
 * Re-init header after every Turbo visit
 */
function initHeader() {
    headerInstance?.destroy();
    headerInstance = new HeaderUI().init();
}

/**
 * Cleanup before Turbo caches the page
 */
function destroyHeader() {
    headerInstance?.destroy();
    headerInstance = null;
}

document.addEventListener('turbo:load', () => {
    // If window.LmkConfig can change between pages, do this first:
    resetLmkConfigCache();

    initHeader()

    theme?.destroy();
    theme = new ThemeSwitcher({
        toggleSelector: ".theme-toggle",
        defaultTheme: "night-theme",
        storageKey: "lakamark.theme",
        getInitialTheme: () => getLmkConfigSafe().preferredTheme,
        eventType: "click",
        usePointerEvents: false,
        useViewTransition: true,
        disabledSelector: "modal.is-open"
    }).init();



});
document.addEventListener('turbo:before-cache', () => {
    destroyHeader()

    // delete the ThemeSwitcher Instance
    theme?.destroy();
    theme = null;

    resetLmkConfigCache();
});

// start Turbo
Turbo.start()