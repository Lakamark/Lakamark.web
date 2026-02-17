import './css/app.scss';

import * as Turbo from "@hotwired/turbo"
import {HeaderUI} from "./lib/HeaderUI.js";

let headerInstance = null;

document.addEventListener('turbo:load', () => {
    headerInstance?.destroy();

    headerInstance = new HeaderUI().init();
});

document.addEventListener('turbo:before-cache', () => {
    headerInstance.destroy();
    headerInstance = null;
})

// start Turbo
Turbo.start()