import './css/app.scss';
import './elements/index.js'

import {getLmkConfigSafe} from "./helpers/config.ts";
import * as Turbo from "@hotwired/turbo"
import {HeaderUI} from "./lib/HeaderUI.js";
import {ThemeSwitcher} from '/elements/ThemeSwitcher.js'


let headerInstance = null;
let themeSwitcher = null;


const config = getLmkConfigSafe();
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
    initHeader()

    // Theme switcher
    themeSwitcher?.destroy();
    themeSwitcher = new ThemeSwitcher({
        defaultTheme: "night-theme",
        toggleSelector: ".theme-toggle",
        duration: 520,
        easing: "ease-out",
        getInitialTheme: () => config.preferredTheme

    }).init();

    // later we can override the theme if the user is logged
    // getInitialTheme: () => document.body.dataset.userTheme || null,
});
document.addEventListener('turbo:before-cache', () => {
    destroyHeader()

    // Reset the themeSwitcher instance
    themeSwitcher?.destroy()
    themeSwitcher = null;
});

// start Turbo
Turbo.start()